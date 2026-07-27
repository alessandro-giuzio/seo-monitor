<?php

namespace App\Services;

use App\Models\GscConnection;
use App\Models\GscMetric;
use App\Models\Website;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GscSyncService
{
    public function syncForWebsite(Website $website, int $days = 28): int
    {
        $connection = $website->gscConnection;
        if (! $connection) {
            throw new RuntimeException('Website is not connected to Google Search Console.');
        }

        if (! $website->gsc_property) {
            throw new RuntimeException('Website has no gsc_property set.');
        }

        $accessToken = $this->freshAccessToken($connection);

        $siteUrl = rawurlencode($website->gsc_property);
        $response = Http::withToken($accessToken)
            ->timeout(30)
            ->post("https://searchconsole.googleapis.com/webmasters/v3/sites/{$siteUrl}/searchAnalytics/query", [
                'startDate' => now()->subDays($days)->toDateString(),
                'endDate' => now()->toDateString(),
                'dimensions' => ['date', 'query', 'page'],
                'rowLimit' => 25000,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Search Console API error: '.$response->body());
        }

        $rows = $response->json('rows', []);
        $created = 0;

        foreach ($rows as $row) {
            [$metricDate, $query, $pageUrl] = $row['keys'] ?? [null, null, null];
            if (! $metricDate) {
                continue;
            }

            GscMetric::updateOrCreate(
                [
                    'website_id' => $website->id,
                    'metric_date' => $metricDate,
                    'query' => $query,
                    'page_url' => $pageUrl,
                ],
                [
                    'clicks' => (int) ($row['clicks'] ?? 0),
                    'impressions' => (int) ($row['impressions'] ?? 0),
                    'ctr' => $row['ctr'] ?? 0,
                    'avg_position' => $row['position'] ?? null,
                ]
            );
            $created++;
        }

        $connection->update(['last_synced_at' => now()]);

        return $created;
    }

    private function freshAccessToken(GscConnection $connection): string
    {
        if ($connection->token_expires_at->isFuture()) {
            return $connection->access_token;
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'refresh_token' => $connection->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Failed to refresh Google access token: '.$response->body());
        }

        $accessToken = $response->json('access_token');
        $expiresIn = (int) $response->json('expires_in', 3600);

        $connection->update([
            'access_token' => $accessToken,
            'token_expires_at' => now()->addSeconds($expiresIn),
        ]);

        return $accessToken;
    }
}
