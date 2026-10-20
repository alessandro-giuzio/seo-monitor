<?php

use App\Models\Keyword;
use App\Models\RankingSnapshot;
use App\Models\SeoAudit;
use App\Models\UptimeCheck;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['services.portal.token' => 'portal-secret']);
});

function portalReportUrl(int $id, ?string $month = '2026-10'): string
{
    return "/api/portal/websites/{$id}/report".($month === null ? '' : "?month={$month}");
}

function reportUptimeCheck(Website $website, string $checkedAt, bool $isUp, ?int $ms = 400): void
{
    UptimeCheck::create([
        'website_id' => $website->id,
        'checked_at' => Carbon::parse($checkedAt),
        'status_code' => $isUp ? 200 : 500,
        'response_time_ms' => $ms,
        'is_up' => $isUp,
    ]);
}

/**
 * @param  array<string, int|null>  $positions  checked_at => position
 */
function reportKeyword(Website $website, string $term, array $positions): void
{
    $keyword = Keyword::create(['website_id' => $website->id, 'term' => $term]);
    foreach ($positions as $checkedAt => $position) {
        RankingSnapshot::create([
            'keyword_id' => $keyword->id,
            'checked_at' => Carbon::parse($checkedAt),
            'position' => $position,
        ]);
    }
}

test('the token is checked before anything else', function () {
    $website = Website::factory()->create();

    $this->getJson(portalReportUrl($website->id))->assertStatus(401);
    $this->withToken('wrong')->getJson(portalReportUrl($website->id))->assertStatus(401);
    $this->getJson(portalReportUrl(999))->assertStatus(401);
    $this->getJson(portalReportUrl($website->id, 'abc'))->assertStatus(401);
});

test('an unknown website is a 404', function () {
    $this->withToken('portal-secret')->getJson(portalReportUrl(999))->assertStatus(404);
});

test('an invalid or missing month is a 422', function (?string $month) {
    $website = Website::factory()->create();

    $this->withToken('portal-secret')
        ->getJson(portalReportUrl($website->id, $month))
        ->assertStatus(422);
})->with(['2026-13', 'abc', '2026-1', null]);

test('a website without data returns null sections', function () {
    $website = Website::factory()->create();

    $this->withToken('portal-secret')
        ->getJson(portalReportUrl($website->id))
        ->assertOk()
        ->assertExactJson(['month' => '2026-10', 'uptime' => null, 'audit' => null, 'keywords' => []]);
});

test('uptime covers the Madrid month only and counts each outage once', function () {
    $website = Website::factory()->create();
    // 23:30 UTC on 30 September is already 1 October in Madrid (UTC+2).
    reportUptimeCheck($website, '2026-09-30T23:30:00Z', false, null);
    reportUptimeCheck($website, '2026-10-05T10:00:00Z', false, null);
    reportUptimeCheck($website, '2026-10-05T10:05:00Z', true, 300);
    reportUptimeCheck($website, '2026-10-12T10:00:00Z', false, null);
    reportUptimeCheck($website, '2026-10-12T10:05:00Z', false, null);
    reportUptimeCheck($website, '2026-10-20T10:00:00Z', true, 500);
    // Outside October in Madrid: 23:30 UTC on 31 October is already 1 November (UTC+1).
    reportUptimeCheck($website, '2026-10-31T23:30:00Z', false, null);
    reportUptimeCheck($website, '2026-09-30T21:30:00Z', false, null);

    $this->withToken('portal-secret')
        ->getJson(portalReportUrl($website->id))
        ->assertOk()
        ->assertJsonPath('uptime', ['uptime_percent' => 33.3, 'avg_response_ms' => 400, 'incidents' => 2]);
});

test('the audit is the latest one by the end of the month, with the score before the month', function () {
    $website = Website::factory()->create();
    foreach (['2026-09-10T10:00:00Z' => 70, '2026-09-25T10:00:00Z' => 84, '2026-10-28T10:00:00Z' => 88, '2026-11-02T10:00:00Z' => 95] as $at => $score) {
        SeoAudit::create([
            'website_id' => $website->id,
            'url' => 'https://example.es',
            'audited_at' => Carbon::parse($at),
            'score' => $score,
        ]);
    }

    $this->withToken('portal-secret')
        ->getJson(portalReportUrl($website->id))
        ->assertOk()
        ->assertJsonPath('audit', [
            'score' => 88,
            'previous_score' => 84,
            'audited_at' => '2026-10-28T10:00:00Z',
        ]);
});

test('keywords show start and end positions, best ten first', function () {
    $website = Website::factory()->create();
    reportKeyword($website, 'fontanero donostia', [
        '2026-09-20T08:00:00Z' => 6,
        '2026-10-15T08:00:00Z' => 4,
        '2026-11-03T08:00:00Z' => 1,
    ]);
    reportKeyword($website, 'nuevo en octubre', ['2026-10-10T08:00:00Z' => 9]);
    reportKeyword($website, 'solo en noviembre', ['2026-11-10T08:00:00Z' => 2]);
    reportKeyword($website, 'fuera del top 100', ['2026-10-10T08:00:00Z' => null]);
    foreach (range(11, 20) as $position) {
        reportKeyword($website, "relleno {$position}", ['2026-10-10T08:00:00Z' => $position]);
    }

    $keywords = $this->withToken('portal-secret')
        ->getJson(portalReportUrl($website->id))
        ->assertOk()
        ->json('keywords');

    expect($keywords)->toHaveCount(10)
        ->and($keywords[0])->toBe(['keyword' => 'fontanero donostia', 'start_position' => 6, 'end_position' => 4])
        ->and($keywords[1])->toBe(['keyword' => 'nuevo en octubre', 'start_position' => null, 'end_position' => 9])
        ->and(collect($keywords)->pluck('keyword'))->not->toContain('solo en noviembre', 'fuera del top 100');
});
