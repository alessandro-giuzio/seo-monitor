<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $websites = Website::query()->orderBy('name')->get();
        $selectedWebsite = null;
        $report = null;

        if ($websites->isNotEmpty()) {
            $selectedId = $request->integer('website_id') ?: $websites->first()->id;
            $selectedWebsite = Website::find($selectedId);
            if ($selectedWebsite) {
                $report = $this->buildReportData($selectedWebsite);
            }
        }

        return view('reports.index', [
            'websites' => $websites,
            'selectedWebsite' => $selectedWebsite,
            'report' => $report,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $request->validate(['website_id' => ['required', 'exists:websites,id']]);
        $website = Website::findOrFail((int) $request->input('website_id'));
        $report = $this->buildReportData($website);

        $filename = 'seo-report-'.$website->id.'-'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($report): void {
            $out = fopen('php://output', 'wb');
            if (! $out) {
                return;
            }

            fputcsv($out, ['Metric', 'Value']);
            fputcsv($out, ['Tracked Keywords', $report['tracked_keywords']]);
            fputcsv($out, ['Top 10 Keywords', $report['top_10_keywords']]);
            fputcsv($out, ['Open Alerts', $report['open_alerts']]);
            fputcsv($out, ['Uptime Last 30d (%)', $report['uptime_rate_30d']]);
            fputcsv($out, ['Latest Crawl Indexable', $report['latest_crawl_indexable']]);
            fputcsv($out, ['Latest Crawl Orphans', $report['latest_crawl_orphans']]);

            fputcsv($out, []);
            fputcsv($out, ['Content Decay URL', 'Prev 28d Clicks', 'Last 28d Clicks', 'Drop %']);
            foreach ($report['decay_rows'] as $row) {
                fputcsv($out, [$row['url'], $row['previous_clicks'], $row['last_clicks'], $row['drop_percent']]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function buildReportData(Website $website): array
    {
        $trackedKeywords = $website->keywords()->count();
        $top10 = $website->keywords()
            ->whereHas('latestSnapshot', fn ($query) => $query->where('position', '<=', 10))
            ->count();
        $openAlerts = $website->seoAlerts()->whereNull('resolved_at')->count();

        $uptimeTotal = $website->uptimeChecks()->where('checked_at', '>=', now()->subDays(30))->count();
        $uptimeHealthy = $website->uptimeChecks()->where('checked_at', '>=', now()->subDays(30))->where('is_up', true)->count();
        $uptimeRate = $uptimeTotal > 0 ? round(($uptimeHealthy / $uptimeTotal) * 100, 1) : null;

        $latestRun = $website->crawlRuns()->latest('started_at')->first();
        $indexable = $latestRun ? $latestRun->pages()->where('is_indexable', true)->count() : 0;
        $orphans = $latestRun ? $latestRun->pages()->where('is_orphan', true)->count() : 0;

        $last28Start = now()->subDays(27)->toDateString();
        $prev28Start = now()->subDays(55)->toDateString();
        $prev28End = now()->subDays(28)->toDateString();

        $lastPeriod = $website->gscMetrics()
            ->whereBetween('metric_date', [$last28Start, now()->toDateString()])
            ->whereNotNull('page_url')
            ->select('page_url', DB::raw('SUM(clicks) as clicks'))
            ->groupBy('page_url')
            ->pluck('clicks', 'page_url');

        $prevPeriod = $website->gscMetrics()
            ->whereBetween('metric_date', [$prev28Start, $prev28End])
            ->whereNotNull('page_url')
            ->select('page_url', DB::raw('SUM(clicks) as clicks'))
            ->groupBy('page_url')
            ->pluck('clicks', 'page_url');

        $decayRows = [];
        foreach ($prevPeriod as $url => $prevClicks) {
            if ((int) $prevClicks < 20) {
                continue;
            }
            $lastClicks = (int) ($lastPeriod[$url] ?? 0);
            $dropPercent = round((($prevClicks - $lastClicks) / $prevClicks) * 100, 1);
            if ($dropPercent >= 20) {
                $decayRows[] = [
                    'url' => $url,
                    'previous_clicks' => (int) $prevClicks,
                    'last_clicks' => $lastClicks,
                    'drop_percent' => $dropPercent,
                ];
            }
        }

        usort($decayRows, fn ($a, $b) => $b['drop_percent'] <=> $a['drop_percent']);

        return [
            'tracked_keywords' => $trackedKeywords,
            'top_10_keywords' => $top10,
            'open_alerts' => $openAlerts,
            'uptime_rate_30d' => $uptimeRate,
            'latest_crawl_indexable' => $indexable,
            'latest_crawl_orphans' => $orphans,
            'decay_rows' => array_slice($decayRows, 0, 20),
        ];
    }
}
