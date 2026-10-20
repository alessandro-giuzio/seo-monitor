<?php

namespace App\Services;

use App\Models\UptimeCheck;
use App\Models\Website;
use Illuminate\Support\Facades\Http;

/**
 * Automatic uptime check: one GET of the website's base URL, stored as an UptimeCheck.
 * The site counts as up when the final response (after redirects) is below 400.
 */
class UptimeMonitorService
{
    public const TIMEOUT_SECONDS = 10;

    public const NOTE = 'Automatic check';

    public function check(Website $website): UptimeCheck
    {
        $started = hrtime(true);

        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->withHeaders(['User-Agent' => 'SEO Toolkit Uptime/1.0'])
                ->get($website->base_url);

            $statusCode = $response->status();
            $isUp = $statusCode < 400;
            $notes = self::NOTE;
        } catch (\Throwable $e) {
            $statusCode = null;
            $isUp = false;
            $notes = self::NOTE.': '.mb_substr($e->getMessage(), 0, 500);
        }

        return $website->uptimeChecks()->create([
            'checked_at' => now(),
            'status_code' => $statusCode,
            // Only meaningful when the server actually answered.
            'response_time_ms' => $statusCode === null ? null : max(1, (int) round((hrtime(true) - $started) / 1_000_000)),
            'is_up' => $isUp,
            'notes' => $notes,
        ]);
    }
}
