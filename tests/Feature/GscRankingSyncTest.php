<?php

use App\Models\GscMetric;
use App\Models\Keyword;
use App\Models\RankingSnapshot;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function gscRow(Website $website, string $date, string $query, int $impressions, float $position): void
{
    GscMetric::factory()->for($website)->create([
        'metric_date' => $date,
        'query' => $query,
        'impressions' => $impressions,
        'avg_position' => $position,
    ]);
}

test('rankings are the impression-weighted 7-day average for matching queries', function () {
    $website = Website::factory()->create();
    $keyword = Keyword::create(['website_id' => $website->id, 'term' => 'Running Shoes']);
    $untracked = Keyword::create(['website_id' => $website->id, 'term' => 'no data']);

    gscRow($website, '2026-10-18', 'running shoes', 100, 4.0);
    gscRow($website, '2026-10-15', 'running shoes', 300, 8.0);   // weighted: (400 + 2400) / 400 = 7
    gscRow($website, '2026-10-01', 'running shoes', 1000, 50.0); // outside the 7-day window
    gscRow($website, '2026-10-18', 'trail shoes', 1000, 1.0);    // different query

    $this->artisan('seo:sync-gsc-rankings')->assertExitCode(0);

    $snapshot = $keyword->rankingSnapshots()->sole();
    expect($snapshot->position)->toBe(7)
        ->and($snapshot->source)->toBe('gsc')
        ->and($snapshot->checked_at->toDateTimeString())->toBe('2026-10-18 12:00:00');
    expect($untracked->rankingSnapshots()->count())->toBe(0);
});

test('running the sync twice for the same day updates instead of duplicating', function () {
    $website = Website::factory()->create();
    $keyword = Keyword::create(['website_id' => $website->id, 'term' => 'shoes']);
    gscRow($website, '2026-10-18', 'shoes', 10, 3.0);

    $this->artisan('seo:sync-gsc-rankings');
    $this->artisan('seo:sync-gsc-rankings');

    expect(RankingSnapshot::query()->where('keyword_id', $keyword->id)->count())->toBe(1);
});
