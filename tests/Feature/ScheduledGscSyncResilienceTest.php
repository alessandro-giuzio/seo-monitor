<?php

use App\Models\GscConnection;
use App\Models\Website;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Regression test: gsc:sync-scheduled must not abort on one bad connection
|--------------------------------------------------------------------------
|
| Following the same pattern as ScheduledCrawlResilienceTest: SyncGscData
| loops connected websites and must continue past one website whose token
| refresh fails (e.g. a revoked/expired refresh token), instead of aborting
| the whole run and leaving every other connected website un-synced.
*/

test('gsc:sync-scheduled continues to the next website after one token refresh fails', function () {
    Http::fake(function ($request) {
        if (str_contains($request->url(), 'oauth2.googleapis.com/token')) {
            $body = $request->data();
            if (($body['refresh_token'] ?? null) === 'bad-refresh-token') {
                return Http::response(['error' => 'invalid_grant'], 400);
            }

            return Http::response(['access_token' => 'new-access-token', 'expires_in' => 3600], 200);
        }

        if (str_contains($request->url(), 'searchconsole.googleapis.com')) {
            return Http::response([
                'rows' => [
                    ['keys' => ['2026-07-01', 'test query', 'https://example.com/page'], 'clicks' => 5, 'impressions' => 100, 'ctr' => 0.05, 'position' => 8.2],
                ],
            ], 200);
        }

        return Http::response([], 404);
    });

    $badWebsite = Website::factory()->create(['gsc_property' => 'sc-domain:bad.example']);
    GscConnection::factory()->for($badWebsite)->create([
        'access_token' => 'stale-access-token',
        'refresh_token' => 'bad-refresh-token',
        'token_expires_at' => now()->subHour(),
    ]);

    $goodWebsite = Website::factory()->create(['gsc_property' => 'sc-domain:good.example']);
    GscConnection::factory()->for($goodWebsite)->create([
        'access_token' => 'stale-access-token',
        'refresh_token' => 'good-refresh-token',
        'token_expires_at' => now()->subHour(),
    ]);

    $this->artisan('gsc:sync-scheduled')->assertExitCode(0);

    expect($badWebsite->gscMetrics()->count())->toBe(0);
    expect($badWebsite->gscConnection->fresh()->last_synced_at)->toBeNull();

    expect($goodWebsite->gscMetrics()->count())->toBe(1);
    expect($goodWebsite->gscConnection->fresh()->last_synced_at)->not->toBeNull();
});
