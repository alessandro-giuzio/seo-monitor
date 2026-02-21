<?php

namespace App\Http\Controllers;

use App\Models\Backlink;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BacklinkController extends Controller
{
    public function index(Request $request): View
    {
        $websites = Website::query()->orderBy('name')->get();
        $selectedWebsite = null;
        $backlinks = collect();

        if ($websites->isNotEmpty()) {
            $selectedId = $request->integer('website_id') ?: $websites->first()->id;
            $selectedWebsite = Website::query()->find($selectedId);

            if ($selectedWebsite) {
                $query = $selectedWebsite->backlinks()->latest('last_seen_at');

                if ($request->boolean('toxic_only')) {
                    $query->where('is_toxic', true);
                }

                if ($request->boolean('nofollow_only')) {
                    $query->where('is_nofollow', true);
                }

                $backlinks = $query->paginate(50)->withQueryString();
            }
        }

        $stats = [
            'total' => $selectedWebsite ? $selectedWebsite->backlinks()->count() : 0,
            'toxic' => $selectedWebsite ? $selectedWebsite->backlinks()->where('is_toxic', true)->count() : 0,
            'nofollow' => $selectedWebsite ? $selectedWebsite->backlinks()->where('is_nofollow', true)->count() : 0,
            'avg_authority' => $selectedWebsite ? round((float) ($selectedWebsite->backlinks()->avg('source_authority') ?? 0), 1) : 0.0,
        ];

        return view('backlinks.index', [
            'websites' => $websites,
            'selectedWebsite' => $selectedWebsite,
            'backlinks' => $backlinks,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'website_id' => ['required', 'exists:websites,id'],
            'source_url' => ['required', 'url', 'max:2048'],
            'target_url' => ['required', 'url', 'max:2048'],
            'anchor_text' => ['nullable', 'string', 'max:255'],
            'source_authority' => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_nofollow' => ['required', 'boolean'],
            'is_toxic' => ['required', 'boolean'],
            'found_at' => ['nullable', 'date'],
            'last_seen_at' => ['nullable', 'date'],
        ]);

        Backlink::create($validated);

        return redirect()
            ->route('backlinks.index', ['website_id' => $validated['website_id']])
            ->with('status', 'Backlink added.');
    }

    public function destroy(Backlink $backlink): RedirectResponse
    {
        $websiteId = $backlink->website_id;
        $backlink->delete();

        return redirect()
            ->route('backlinks.index', ['website_id' => $websiteId])
            ->with('status', 'Backlink removed.');
    }
}
