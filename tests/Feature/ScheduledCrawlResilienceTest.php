<?php

use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Regression test for the scheduled crawler killing an entire hourly run
|--------------------------------------------------------------------------
|
| SeoCrawlerService made unguarded Http::get() calls with no try/catch, so
| one unreachable website threw a ConnectionException that aborted
| `seo:run-scheduled` entirely, silently skipping every other due website
| in that run (see PLAN.md). The fix wraps the crawl in try/catch per
| website and marks the failed run as such instead of leaving it "running"
| forever. This test reproduces the original failure condition and asserts
| the command still crawls the next website successfully.
*/

test('seo:run-scheduled continues to the next website after one fails', function () {
    Http::fake([
        'unreachable.test*' => fn () => throw new ConnectionException('Could not resolve host'),
        'reachable.test*' => Http::response('<html><head><title>OK</title></head><body>Hello world</body></html>', 200),
    ]);

    $badWebsite = Website::factory()->create(['base_url' => 'https://unreachable.test']);
    $goodWebsite = Website::factory()->create(['base_url' => 'https://reachable.test']);

    $this->artisan('seo:run-scheduled')->assertExitCode(0);

    expect($badWebsite->crawlRuns()->latest('started_at')->first()->status)->toBe('failed');
    expect($goodWebsite->crawlRuns()->latest('started_at')->first()->status)->toBe('completed');
});
