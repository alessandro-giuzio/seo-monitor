<?php

namespace App\Http\Controllers;

use App\Models\ReleaseQaRun;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReleaseQaController extends Controller
{
    public function index(Request $request): View
    {
        $websites = Website::query()->orderBy('name')->get();
        $selectedWebsite = null;
        $runs = collect();

        if ($websites->isNotEmpty()) {
            $selectedId = $request->integer('website_id') ?: $websites->first()->id;
            $selectedWebsite = Website::find($selectedId);
            if ($selectedWebsite) {
                $runs = $selectedWebsite->releaseQaRuns()->paginate(30)->withQueryString();
            }
        }

        return view('release-qa.index', [
            'websites' => $websites,
            'selectedWebsite' => $selectedWebsite,
            'runs' => $runs,
        ]);
    }

    public function run(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'website_id' => ['required', 'exists:websites,id'],
            'environment' => ['required', 'in:staging,production,preview'],
            'release_tag' => ['nullable', 'string', 'max:255'],
        ]);

        $website = Website::findOrFail($validated['website_id']);
        $latestCrawl = $website->crawlRuns()->latest('started_at')->first();
        $latestAudit = $website->seoAudits()->latest('audited_at')->first();

        $issues = [];

        if (! $latestCrawl) {
            $issues[] = $this->issue('crawlability', 'high', 'No technical crawl data available', 'Run a crawl before release QA.');
        } else {
            $orphanPages = $latestCrawl->pages()->where('is_orphan', true)->count();
            $nonIndexable = $latestCrawl->pages()->where('is_indexable', false)->count();
            $brokenPages = $latestCrawl->pages()->where('status_code', '>=', 400)->count();

            if ($orphanPages > 0) {
                $issues[] = $this->issue('crawlability', 'medium', 'Orphan pages detected', "{$orphanPages} orphan page(s) in latest crawl.");
            }
            if ($nonIndexable > 0) {
                $issues[] = $this->issue('indexation', 'high', 'Non-indexable pages detected', "{$nonIndexable} page(s) are non-indexable.");
            }
            if ($brokenPages > 0) {
                $issues[] = $this->issue('crawlability', 'high', 'Broken pages detected', "{$brokenPages} page(s) returning 4xx/5xx.");
            }
        }

        if (! $latestAudit) {
            $issues[] = $this->issue('onpage', 'medium', 'No recent on-page audit', 'Run on-page audit for critical templates.');
        } elseif ($latestAudit->score < 70) {
            $issues[] = $this->issue('onpage', 'high', 'Low audit score', "Latest audit score is {$latestAudit->score}/100.", $latestAudit->url);
        }

        $openAlerts = $website->seoAlerts()->whereNull('resolved_at')->count();
        if ($openAlerts > 0) {
            $issues[] = $this->issue('alerts', 'medium', 'Open SEO alerts exist', "{$openAlerts} unresolved alert(s) found.");
        }

        $redirectIssues = $website->redirectRules()
            ->where('is_active', true)
            ->whereNotNull('last_check_result')
            ->where('last_check_result', '!=', 'ok')
            ->count();
        if ($redirectIssues > 0) {
            $issues[] = $this->issue('redirects', 'high', 'Redirect validation failures', "{$redirectIssues} redirect rule(s) currently failing validation.");
        }

        $high = collect($issues)->where('severity', 'high')->count();
        $medium = collect($issues)->where('severity', 'medium')->count();
        $score = max(0, 100 - ($high * 20) - ($medium * 8));
        $status = $high > 0 ? 'fail' : ($medium > 0 ? 'warn' : 'pass');

        $run = ReleaseQaRun::create([
            'website_id' => $website->id,
            'checked_at' => now(),
            'environment' => $validated['environment'],
            'release_tag' => $validated['release_tag'] ?? null,
            'status' => $status,
            'score' => $score,
            'summary' => [
                'high_issues' => $high,
                'medium_issues' => $medium,
                'total_issues' => count($issues),
                'latest_crawl_id' => $latestCrawl?->id,
                'latest_audit_id' => $latestAudit?->id,
            ],
        ]);

        if (! empty($issues)) {
            $run->issues()->createMany(array_map(fn ($item) => $item + ['website_id' => $website->id], $issues));
        }

        return redirect()
            ->route('release-qa.show', $run)
            ->with('status', 'Release QA run completed.');
    }

    public function show(ReleaseQaRun $run): View
    {
        $run->load(['website', 'issues']);

        return view('release-qa.show', ['run' => $run]);
    }

    private function issue(string $category, string $severity, string $title, string $details, ?string $url = null): array
    {
        return [
            'category' => $category,
            'severity' => $severity,
            'title' => $title,
            'details' => $details,
            'url' => $url,
        ];
    }
}
