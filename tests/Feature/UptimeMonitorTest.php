<?php

use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('seo:check-uptime records up, down and unreachable websites', function () {
    Http::fake([
        'up.test*' => Http::response('ok', 200),
        'error.test*' => Http::response('boom', 503),
        'unreachable.test*' => fn () => throw new ConnectionException('Could not resolve host'),
    ]);

    $up = Website::factory()->create(['base_url' => 'https://up.test']);
    $error = Website::factory()->create(['base_url' => 'https://error.test']);
    $unreachable = Website::factory()->create(['base_url' => 'https://unreachable.test']);

    $this->artisan('seo:check-uptime')->assertExitCode(0);

    $upCheck = $up->uptimeChecks()->sole();
    expect($upCheck->is_up)->toBeTrue()
        ->and($upCheck->status_code)->toBe(200)
        ->and($upCheck->response_time_ms)->toBeGreaterThan(0);

    $errorCheck = $error->uptimeChecks()->sole();
    expect($errorCheck->is_up)->toBeFalse()->and($errorCheck->status_code)->toBe(503);

    $downCheck = $unreachable->uptimeChecks()->sole();
    expect($downCheck->is_up)->toBeFalse()
        ->and($downCheck->status_code)->toBeNull()
        ->and($downCheck->response_time_ms)->toBeNull()
        ->and($downCheck->notes)->toContain('Could not resolve host');
});
