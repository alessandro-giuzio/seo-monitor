<?php

namespace App\Http\Controllers;

use App\Models\CrawlRun;
use App\Models\Website;
use App\Services\SeoCrawlerService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TechnicalSeoController extends Controller
{
    public function index(Request $request): View
    {
        $websites = Website::query()->orderBy('name')->get();
        $selectedWebsite = null;
        $latestRun = null;
        $pages = collect();

        if ($websites->isNotEmpty()) {
            $selectedId = $request->integer('website_id') ?: $websites->first()->id;
            $selectedWebsite = Website::query()->find($selectedId);
            if ($selectedWebsite) {
                $latestRun = $selectedWebsite->crawlRuns()->latest('started_at')->first();
                if ($latestRun) {
                    $pages = $latestRun->pages()->orderByDesc('is_orphan')->orderBy('url')->paginate(100)->withQueryString();
                }
            }
        }

        return view('technical.index', [
            'websites' => $websites,
            'selectedWebsite' => $selectedWebsite,
            'latestRun' => $latestRun,
            'pages' => $pages,
        ]);
    }

    public function run(Request $request, SeoCrawlerService $crawlerService): RedirectResponse
    {
        $validated = $request->validate([
            'website_id' => ['required', 'exists:websites,id'],
            'max_pages' => ['nullable', 'integer', 'min:5', 'max:200'],
        ]);

        $website = Website::findOrFail($validated['website_id']);
        $run = $crawlerService->runForWebsite($website, (int) ($validated['max_pages'] ?? 30));

        return redirect()
            ->route('technical.index', ['website_id' => $website->id])
            ->with('status', "Technical scan completed. {$run->pages_crawled} pages crawled.");
    }

    public function show(CrawlRun $run): View
    {
        $run->load([
            'website',
            'pages' => fn ($query) => $query->orderByDesc('is_orphan')->orderBy('url'),
            'linkOpportunities' => fn ($query) => $query->orderByDesc('priority_score'),
        ]);

        return view('technical.run', ['run' => $run]);
    }
}
