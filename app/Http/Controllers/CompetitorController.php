<?php

namespace App\Http\Controllers;

use App\Models\Competitor;
use App\Models\Website;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CompetitorController extends Controller
{
    public function index(Request $request): View
    {
        $websites = Website::query()->orderBy('name')->get();
        $selectedWebsite = null;
        $gapRows = collect();

        if ($websites->isNotEmpty()) {
            $selectedId = $request->integer('website_id') ?: $websites->first()->id;
            $selectedWebsite = Website::query()
                ->with([
                    'keywords.latestSnapshot',
                    'competitors.keywordSnapshots' => fn (Builder $query) => $query->latest('checked_at')->limit(300),
                ])
                ->find($selectedId);

            if ($selectedWebsite) {
                $ownPositions = [];
                foreach ($selectedWebsite->keywords as $keyword) {
                    if ($keyword->latestSnapshot?->position !== null) {
                        $ownPositions[strtolower($keyword->term)] = (int) $keyword->latestSnapshot->position;
                    }
                }

                $rows = [];
                foreach ($selectedWebsite->competitors as $competitor) {
                    $latestByKeyword = [];
                    foreach ($competitor->keywordSnapshots as $snapshot) {
                        $normalizedKeyword = strtolower($snapshot->keyword);
                        if (! isset($latestByKeyword[$normalizedKeyword])) {
                            $latestByKeyword[$normalizedKeyword] = $snapshot;
                        }
                    }

                    foreach ($latestByKeyword as $normalizedKeyword => $snapshot) {
                        $competitorPosition = $snapshot->position;
                        if ($competitorPosition === null || $competitorPosition > 30) {
                            continue;
                        }

                        $ownPosition = $ownPositions[$normalizedKeyword] ?? null;
                        if ($ownPosition === null || $ownPosition > $competitorPosition) {
                            $rows[] = [
                                'keyword' => $snapshot->keyword,
                                'competitor' => $competitor->name,
                                'competitor_position' => $competitorPosition,
                                'your_position' => $ownPosition,
                                'search_volume' => $snapshot->search_volume,
                                'gap' => $ownPosition ? $ownPosition - $competitorPosition : null,
                            ];
                        }
                    }
                }

                $gapRows = collect($rows)
                    ->sortBy([
                        ['search_volume', 'desc'],
                        ['competitor_position', 'asc'],
                    ])
                    ->values()
                    ->take(200);
            }
        }

        return view('competitors.index', [
            'websites' => $websites,
            'selectedWebsite' => $selectedWebsite,
            'gapRows' => $gapRows,
        ]);
    }

    public function store(Request $request, Website $website): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $website->competitors()->create($validated);

        return redirect()
            ->route('competitors.index', ['website_id' => $website->id])
            ->with('status', 'Competitor added.');
    }

    public function storeSnapshot(Request $request, Competitor $competitor): RedirectResponse
    {
        $validated = $request->validate([
            'keyword' => ['required', 'string', 'max:255'],
            'checked_at' => ['required', 'date'],
            'position' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'search_volume' => ['nullable', 'integer', 'min:0'],
        ]);

        $competitor->keywordSnapshots()->create($validated);

        return redirect()
            ->route('competitors.index', ['website_id' => $competitor->website_id])
            ->with('status', 'Competitor ranking snapshot added.');
    }

    public function destroy(Competitor $competitor): RedirectResponse
    {
        $websiteId = $competitor->website_id;
        $competitor->delete();

        return redirect()
            ->route('competitors.index', ['website_id' => $websiteId])
            ->with('status', 'Competitor removed.');
    }
}
