<?php

namespace App\Services;

use App\Models\Keyword;
use App\Models\RankingSnapshot;
use App\Models\SeoAudit;
use App\Models\UptimeCheck;
use App\Models\Website;
use Carbon\CarbonImmutable;

/**
 * Lists what's missing for a website's monthly portal report (see PortalReportService),
 * so it can be fixed before the portal drafts the report on the 1st.
 * Months are Madrid calendar months, like the report itself.
 */
class ReportReadinessService
{
    /** Fewer uptime checks than this per elapsed day means the uptime figure isn't trustworthy. */
    public const MIN_UPTIME_CHECKS_PER_DAY = 1;

    public const GSC_STALE_DAYS = 3;

    /**
     * @return list<string> human-readable problems; empty when the report is ready
     */
    public function issuesForMonth(Website $website, string $month): array
    {
        $tz = PortalReportService::TIMEZONE;
        $start = CarbonImmutable::createFromFormat('!Y-m', $month, $tz)->utc();
        $end = CarbonImmutable::createFromFormat('!Y-m', $month, $tz)->addMonth()->utc();
        $until = now()->lessThan($end) ? now() : $end;
        $issues = [];

        $checks = UptimeCheck::query()
            ->where('website_id', $website->id)
            ->where('checked_at', '>=', $start)
            ->where('checked_at', '<', $end)
            ->count();
        $daysElapsed = max(1, (int) ceil($start->diffInDays($until)));
        if ($checks === 0) {
            $issues[] = 'No uptime checks this month.';
        } elseif ($checks < $daysElapsed * self::MIN_UPTIME_CHECKS_PER_DAY) {
            $issues[] = "Only {$checks} uptime check(s) in {$daysElapsed} day(s) — the uptime % won't mean much.";
        }

        $audited = SeoAudit::query()
            ->where('website_id', $website->id)
            ->where('audited_at', '>=', $start)
            ->where('audited_at', '<', $end)
            ->exists();
        if (! $audited) {
            $issues[] = 'No audit attached to this website this month.';
        }

        $keywords = Keyword::query()->where('website_id', $website->id)->get(['id', 'term']);
        if ($keywords->isEmpty()) {
            $issues[] = 'No tracked keywords — the report will have no rankings.';
        } else {
            $ranked = RankingSnapshot::query()
                ->whereIn('keyword_id', $keywords->pluck('id'))
                ->where('checked_at', '>=', $start)
                ->where('checked_at', '<', $end)
                ->whereNotNull('position')
                ->distinct()
                ->pluck('keyword_id');
            $missing = $keywords->whereNotIn('id', $ranked->all());
            if ($missing->isNotEmpty()) {
                $issues[] = "{$missing->count()} of {$keywords->count()} keyword(s) have no ranking this month: "
                    .$missing->pluck('term')->take(10)->implode(', ').($missing->count() > 10 ? ', …' : '');
            }
        }

        $connection = $website->gscConnection;
        if ($connection === null) {
            $issues[] = 'Search Console not connected — keyword rankings must be logged by hand.';
        } elseif ($connection->last_synced_at === null || $connection->last_synced_at->lt(now()->subDays(self::GSC_STALE_DAYS))) {
            $issues[] = 'Search Console last synced '.($connection->last_synced_at?->diffForHumans() ?? 'never').'.';
        }

        return $issues;
    }
}
