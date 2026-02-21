<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DomainOverviewController extends Controller
{
    public function index(Request $request): View
    {
        $websites = Website::query()->orderBy('name')->get();
        $selectedWebsite = null;
        $snapshots = collect();

        if ($websites->isNotEmpty()) {
            $selectedId = $request->integer('website_id') ?: $websites->first()->id;
            $selectedWebsite = Website::query()
                ->with([
                    'domainMetricsSnapshots' => fn ($query) => $query->latest('snapshot_at')->limit(30),
                ])
                ->find($selectedId);

            $snapshots = $selectedWebsite?->domainMetricsSnapshots->sortBy('snapshot_at')->values() ?? collect();
        }

        return view('domain-overview.index', [
            'websites' => $websites,
            'selectedWebsite' => $selectedWebsite,
            'snapshots' => $snapshots,
        ]);
    }

    public function store(Request $request, Website $website): RedirectResponse
    {
        $validated = $request->validate([
            'snapshot_at' => ['required', 'date'],
            'estimated_traffic' => ['nullable', 'integer', 'min:0'],
            'organic_keywords' => ['nullable', 'integer', 'min:0'],
            'referring_domains' => ['nullable', 'integer', 'min:0'],
            'backlinks_count' => ['nullable', 'integer', 'min:0'],
            'visibility_index' => ['nullable', 'integer', 'min:0', 'max:100'],
            'avg_position' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $website->domainMetricsSnapshots()->create($validated);

        return redirect()
            ->route('domain-overview.index', ['website_id' => $website->id])
            ->with('status', 'Domain metrics snapshot added.');
    }
}
