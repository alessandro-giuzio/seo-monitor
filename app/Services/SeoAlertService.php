<?php

namespace App\Services;

use App\Models\SeoAlert;
use App\Models\Website;
use Illuminate\Support\Carbon;

class SeoAlertService
{
    public function evaluateForWebsite(Website $website): int
    {
        $created = 0;
        $now = now();

        $latestRun = $website->crawlRuns()->latest('started_at')->first();
        if ($latestRun) {
            $orphanPages = $latestRun->pages()->where('is_orphan', true)->count();
            $notIndexablePages = $latestRun->pages()->where('is_indexable', false)->count();

            if ($orphanPages > 0) {
                $created += $this->createAlertIfMissing(
                    $website,
                    'orphan_pages',
                    'medium',
                    'Orphan pages detected',
                    "{$orphanPages} orphan page(s) found in the latest crawl.",
                    ['crawl_run_id' => $latestRun->id, 'count' => $orphanPages],
                    $now
                );
            }

            if ($notIndexablePages > 0) {
                $created += $this->createAlertIfMissing(
                    $website,
                    'indexation',
                    'high',
                    'Indexation issues detected',
                    "{$notIndexablePages} page(s) are currently non-indexable.",
                    ['crawl_run_id' => $latestRun->id, 'count' => $notIndexablePages],
                    $now
                );
            }
        }

        $created += $this->detectTrafficDrops($website, $now);

        return $created;
    }

    private function detectTrafficDrops(Website $website, Carbon $now): int
    {
        $last28Start = $now->copy()->subDays(27)->startOfDay();
        $prev28Start = $now->copy()->subDays(55)->startOfDay();
        $prev28End = $now->copy()->subDays(28)->endOfDay();

        $lastPeriod = $website->gscMetrics()
            ->whereBetween('metric_date', [$last28Start->toDateString(), $now->toDateString()])
            ->selectRaw('page_url, SUM(clicks) as clicks')
            ->groupBy('page_url')
            ->pluck('clicks', 'page_url');

        if ($lastPeriod->isEmpty()) {
            return 0;
        }

        $prevPeriod = $website->gscMetrics()
            ->whereBetween('metric_date', [$prev28Start->toDateString(), $prev28End->toDateString()])
            ->selectRaw('page_url, SUM(clicks) as clicks')
            ->groupBy('page_url')
            ->pluck('clicks', 'page_url');

        $created = 0;

        foreach ($lastPeriod as $pageUrl => $lastClicks) {
            $previousClicks = (int) ($prevPeriod[$pageUrl] ?? 0);
            if ($previousClicks < 20) {
                continue;
            }

            $dropPercent = 100 * (($previousClicks - (int) $lastClicks) / $previousClicks);
            if ($dropPercent >= 30) {
                $created += $this->createAlertIfMissing(
                    $website,
                    'traffic_drop',
                    'high',
                    'Content decay risk',
                    "{$pageUrl} dropped by ".round($dropPercent, 1)."% in clicks over the last 28 days.",
                    [
                        'page_url' => $pageUrl,
                        'previous_clicks' => $previousClicks,
                        'last_clicks' => (int) $lastClicks,
                        'drop_percent' => round($dropPercent, 1),
                    ],
                    $now
                );
            }
        }

        return $created;
    }

    private function createAlertIfMissing(
        Website $website,
        string $type,
        string $severity,
        string $title,
        string $message,
        array $meta,
        Carbon $now
    ): int {
        $duplicate = SeoAlert::query()
            ->where('website_id', $website->id)
            ->where('type', $type)
            ->where('message', $message)
            ->whereNull('resolved_at')
            ->where('detected_at', '>=', $now->copy()->subDay())
            ->exists();

        if ($duplicate) {
            return 0;
        }

        SeoAlert::create([
            'website_id' => $website->id,
            'type' => $type,
            'severity' => $severity,
            'title' => $title,
            'message' => $message,
            'meta' => $meta,
            'detected_at' => $now,
        ]);

        return 1;
    }
}
