<?php

use App\Models\SeoAudit;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('seo:audit-scheduled audits each base URL and survives a failing website', function () {
    Http::fake([
        'unreachable.test*' => fn () => throw new ConnectionException('Could not resolve host'),
        'reachable.test*' => Http::response('<html><head><title>Hello</title></head><body><h1>Hi</h1></body></html>', 200),
    ]);

    $bad = Website::factory()->create(['base_url' => 'https://unreachable.test']);
    $good = Website::factory()->create(['base_url' => 'https://reachable.test']);

    $this->artisan('seo:audit-scheduled')->assertExitCode(0);

    expect($bad->seoAudits()->count())->toBe(0);
    $audit = $good->seoAudits()->sole();
    expect($audit->url)->toBe('https://reachable.test')
        ->and($audit->title)->toBe('Hello')
        ->and($audit->h1_count)->toBe(1);
});

test('the audit form still audits pasted HTML and reports unreachable URLs', function () {
    Http::fake(['unreachable.test*' => fn () => throw new ConnectionException('Could not resolve host')]);
    $user = User::factory()->create();
    $website = Website::factory()->create();

    $this->actingAs($user)->post(route('audits.store'), [
        'website_id' => $website->id,
        'url' => 'https://example.test/page',
        'raw_html' => '<html><head><title>Pasted</title></head><body></body></html>',
    ])->assertRedirect();

    expect(SeoAudit::query()->sole())
        ->website_id->toBe($website->id)
        ->title->toBe('Pasted');

    $this->actingAs($user)->from(route('audits.index'))->post(route('audits.store'), [
        'url' => 'https://unreachable.test',
    ])->assertRedirect(route('audits.index'))->assertSessionHasErrors('url');
});
