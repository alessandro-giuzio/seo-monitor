<?php

namespace App\Services;

use App\Models\Keyword;
use App\Models\RankingSnapshot;
use App\Models\SeoAudit;
use App\Models\UptimeCheck;
use App\Models\Website;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Monthly website figures for the client portal's reports
 * (GET /api/portal/websites/{id}/report?month=YYYY-MM).
 *
 * A month is a calendar month in Madrid time, converted to UTC for the queries.
 * Queries use Model::query() rather than Website relations, whose default ORDER BY
 * PostgreSQL rejects in aggregate queries.
 */
class PortalReportService
{
    public const KEYWORD_LIMIT = 10;

    public const TIMEZONE = 'Europe/Madrid';

    /**
     * @return array<string, mixed>
     */
    public function forMonth(Website $website, string $month): array
    {
        $start = CarbonImmutable::createFromFormat('!Y-m', $month, self::TIMEZONE)->utc();
        $end = CarbonImmutable::createFromFormat('!Y-m', $month, self::TIMEZONE)->addMonth()->utc();

        return [
            'month' => $month,
            'uptime' => $this->uptime($website, $start, $end),
            'audit' => $this->audit($website, $start, $end),
            'keywords' => $this->keywords($website, $start, $end),
        ];
    }

    /**
     * @return array{uptime_percent: float, avg_response_ms: int|null, incidents: int}|null
     */
    private function uptime(Website $website, CarbonInterface $start, CarbonInterface $end): ?array
    {
        $checks = UptimeCheck::query()
            ->where('website_id', $website->id)
            ->where('checked_at', '>=', $start)
            ->where('checked_at', '<', $end)
            ->orderBy('checked_at')
            ->get(['is_up', 'response_time_ms']);
        if ($checks->isEmpty()) {
            return null;
        }

        // An incident is a run of failed checks: count each change from up (or the start) to down.
        $incidents = 0;
        $wasUp = true;
        foreach ($checks as $check) {
            if ($wasUp && ! $check->is_up) {
                $incidents++;
            }
            $wasUp = (bool) $check->is_up;
        }
        $timed = $checks->whereNotNull('response_time_ms');

        return [
            'uptime_percent' => round($checks->where('is_up', true)->count() / $checks->count() * 100, 1),
            'avg_response_ms' => $timed->isEmpty() ? null : (int) round($timed->avg('response_time_ms')),
            'incidents' => $incidents,
        ];
    }

    /**
     * @return array{score: int, previous_score: int|null, audited_at: string}|null
     */
    private function audit(Website $website, CarbonInterface $start, CarbonInterface $end): ?array
    {
        $latestBefore = fn (CarbonInterface $moment) => SeoAudit::query()
            ->where('website_id', $website->id)
            ->where('audited_at', '<', $moment)
            ->orderByDesc('audited_at')
            ->first();

        $audit = $latestBefore($end);
        if ($audit === null) {
            return null;
        }
        $previous = $latestBefore($start);

        return [
            'score' => (int) $audit->score,
            'previous_score' => $previous === null ? null : (int) $previous->score,
            'audited_at' => $audit->audited_at->copy()->utc()->toIso8601ZuluString(),
        ];
    }

    /**
     * @return list<array{keyword: string, start_position: int|null, end_position: int}>
     */
    private function keywords(Website $website, CarbonInterface $start, CarbonInterface $end): array
    {
        $positionBefore = fn (Keyword $keyword, CarbonInterface $moment) => RankingSnapshot::query()
            ->where('keyword_id', $keyword->id)
            ->where('checked_at', '<', $moment)
            ->orderByDesc('checked_at')
            ->value('position');

        return Keyword::query()
            ->where('website_id', $website->id)
            ->get()
            ->map(fn (Keyword $keyword) => [
                'keyword' => $keyword->term,
                'start' => $positionBefore($keyword, $start),
                'end' => $positionBefore($keyword, $end),
            ])
            ->filter(fn (array $row) => $row['end'] !== null)
            ->sortBy('end')
            ->take(self::KEYWORD_LIMIT)
            ->map(fn (array $row) => [
                'keyword' => $row['keyword'],
                'start_position' => $row['start'] === null ? null : (int) $row['start'],
                'end_position' => (int) $row['end'],
            ])
            ->values()
            ->all();
    }
}
