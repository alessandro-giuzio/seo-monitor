<?php

namespace App\Services;

use App\Models\Keyword;
use App\Models\RankingSnapshot;
use App\Models\SeoAlert;
use App\Models\SeoAudit;
use App\Models\UptimeCheck;
use App\Models\Website;
use Carbon\CarbonInterface;

/**
 * Read-only website summary for the client portal (GET /api/portal/websites/{id}/status).
 *
 * Queries the models directly rather than Website relations: several relations carry a
 * default ORDER BY, which PostgreSQL rejects in aggregate queries.
 */
class PortalStatusService
{
    public const KEYWORD_LIMIT = 5;

    public const UPTIME_WINDOW_DAYS = 30;

    /**
     * @return array<string, mixed>
     */
    public function forWebsite(Website $website): array
    {
        return [
            'website' => [
                'id' => $website->id,
                'name' => $website->name,
                'base_url' => $website->base_url,
            ],
            'uptime' => $this->uptime($website),
            'audit' => $this->audit($website),
            'open_alerts' => SeoAlert::query()
                ->where('website_id', $website->id)
                ->whereNull('resolved_at')
                ->count(),
            'keywords' => $this->keywords($website),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function uptime(Website $website): ?array
    {
        $latest = UptimeCheck::query()
            ->where('website_id', $website->id)
            ->orderByDesc('checked_at')
            ->first();
        if ($latest === null) {
            return null;
        }

        $window = fn () => UptimeCheck::query()
            ->where('website_id', $website->id)
            ->where('checked_at', '>=', now()->subDays(self::UPTIME_WINDOW_DAYS));
        $total = $window()->count();
        $up = $window()->where('is_up', true)->count();
        $average = $window()->whereNotNull('response_time_ms')->avg('response_time_ms');

        return [
            'is_up' => (bool) $latest->is_up,
            'last_checked_at' => $this->iso($latest->checked_at),
            'uptime_percent_30d' => $total > 0 ? round($up / $total * 100, 1) : null,
            'avg_response_ms_30d' => $average !== null ? (int) round((float) $average) : null,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function audit(Website $website): ?array
    {
        $audit = SeoAudit::query()
            ->where('website_id', $website->id)
            ->orderByDesc('audited_at')
            ->first();

        return $audit === null
            ? null
            : ['score' => (int) $audit->score, 'audited_at' => $this->iso($audit->audited_at)];
    }

    /**
     * @return list<array{keyword: string, position: int, previous_position: int|null}>
     */
    private function keywords(Website $website): array
    {
        return Keyword::query()
            ->where('website_id', $website->id)
            ->with('latestSnapshot')
            ->get()
            ->filter(fn (Keyword $keyword) => $keyword->latestSnapshot?->position !== null)
            ->sortBy(fn (Keyword $keyword) => $keyword->latestSnapshot->position)
            ->take(self::KEYWORD_LIMIT)
            ->map(fn (Keyword $keyword) => [
                'keyword' => $keyword->term,
                'position' => (int) $keyword->latestSnapshot->position,
                'previous_position' => $this->previousPosition($keyword),
            ])
            ->values()
            ->all();
    }

    private function previousPosition(Keyword $keyword): ?int
    {
        $position = RankingSnapshot::query()
            ->where('keyword_id', $keyword->id)
            ->where('checked_at', '<', $keyword->latestSnapshot->checked_at)
            ->orderByDesc('checked_at')
            ->value('position');

        return $position === null ? null : (int) $position;
    }

    private function iso(CarbonInterface $date): string
    {
        return $date->copy()->utc()->toIso8601ZuluString();
    }
}
