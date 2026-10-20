<?php

namespace App\Services;

use App\Models\GscMetric;
use App\Models\Keyword;
use App\Models\RankingSnapshot;
use App\Models\Website;
use Illuminate\Support\Carbon;

/**
 * Turns imported Search Console rows into ranking snapshots for tracked keywords.
 *
 * For each keyword whose term matches a GSC query (case-insensitive), the position is the
 * impression-weighted average position over the 7 days ending at the latest imported date.
 * One snapshot per keyword per day (source 'gsc'); re-running the same day updates it.
 * Queries GscMetric directly rather than Website::gscMetrics(), which carries a default ORDER BY.
 */
class GscRankingService
{
    public const WINDOW_DAYS = 7;

    public const SOURCE = 'gsc';

    /**
     * @return int number of keywords that got a snapshot
     */
    public function syncForWebsite(Website $website): int
    {
        $latest = GscMetric::query()
            ->where('website_id', $website->id)
            ->whereNotNull('query')
            ->max('metric_date');
        if ($latest === null) {
            return 0;
        }

        $end = Carbon::parse($latest)->startOfDay();
        $start = $end->copy()->subDays(self::WINDOW_DAYS - 1);
        // Midday UTC keeps the snapshot on the same calendar day in Madrid for monthly reports.
        $checkedAt = $end->copy()->setTime(12, 0);

        $synced = 0;

        foreach (Keyword::query()->where('website_id', $website->id)->get() as $keyword) {
            $rows = GscMetric::query()
                ->where('website_id', $website->id)
                // Half-open range: SQLite stores the date cast as 'Y-m-d 00:00:00', which a
                // BETWEEN ... 'Y-m-d' upper bound would exclude.
                ->where('metric_date', '>=', $start->toDateString())
                ->where('metric_date', '<', $end->copy()->addDay()->toDateString())
                ->whereRaw('LOWER(query) = ?', [mb_strtolower(trim($keyword->term))])
                ->whereNotNull('avg_position')
                ->get(['impressions', 'avg_position']);

            $impressions = (int) $rows->sum('impressions');
            if ($impressions === 0) {
                continue;
            }

            $weighted = $rows->sum(fn (GscMetric $row) => (float) $row->avg_position * $row->impressions);

            RankingSnapshot::updateOrCreate(
                ['keyword_id' => $keyword->id, 'source' => self::SOURCE, 'checked_at' => $checkedAt],
                [
                    'position' => max(1, (int) round($weighted / $impressions)),
                    'notes' => 'Search Console, '.self::WINDOW_DAYS."-day average ({$impressions} impressions)",
                ]
            );
            $synced++;
        }

        return $synced;
    }
}
