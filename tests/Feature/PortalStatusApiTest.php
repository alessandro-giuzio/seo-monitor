<?php

use App\Models\Keyword;
use App\Models\RankingSnapshot;
use App\Models\SeoAlert;
use App\Models\SeoAudit;
use App\Models\UptimeCheck;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['services.portal.token' => 'portal-secret']);
});

function portalStatusUrl(int $id): string
{
    return "/api/portal/websites/{$id}/status";
}

/**
 * @param  array<string, int|null>  $positions  checked_at => position
 */
function trackKeyword(Website $website, string $term, array $positions): void
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

test('a missing token is rejected', function () {
    $website = Website::factory()->create();

    $this->getJson(portalStatusUrl($website->id))->assertStatus(401);
});

test('a wrong token is rejected', function () {
    $website = Website::factory()->create();

    $this->withToken('wrong')->getJson(portalStatusUrl($website->id))->assertStatus(401);
});

test('every request is rejected when no token is configured', function () {
    config(['services.portal.token' => null]);
    $website = Website::factory()->create();

    $this->getJson(portalStatusUrl($website->id))->assertStatus(401);
    $this->withToken('')->getJson(portalStatusUrl($website->id))->assertStatus(401);
});

test('an unknown website without a token is a 401, not a 404', function () {
    $this->getJson(portalStatusUrl(999))->assertStatus(401);
});

test('an unknown website is a 404', function () {
    $this->withToken('portal-secret')->getJson(portalStatusUrl(999))->assertStatus(404);
});

test('a website without data returns null sections', function () {
    $website = Website::factory()->create([
        'name' => 'Fontanería Ejemplo',
        'base_url' => 'https://example.es',
    ]);

    $this->withToken('portal-secret')
        ->getJson(portalStatusUrl($website->id))
        ->assertOk()
        ->assertExactJson([
            'website' => [
                'id' => $website->id,
                'name' => 'Fontanería Ejemplo',
                'base_url' => 'https://example.es',
            ],
            'uptime' => null,
            'audit' => null,
            'open_alerts' => 0,
            'keywords' => [],
        ]);
});

test('a website with data returns the documented status', function () {
    $this->travelTo(Carbon::parse('2026-09-24T10:00:00Z'));
    $website = Website::factory()->create([
        'name' => 'Fontanería Ejemplo',
        'base_url' => 'https://example.es',
    ]);
    $other = Website::factory()->create();

    // Uptime: the check outside the 30-day window and the other website's check are ignored.
    foreach ([
        ['2026-08-01T08:00:00Z', false, 900],
        ['2026-09-21T08:00:00Z', true, 400],
        ['2026-09-22T08:00:00Z', false, null],
        ['2026-09-23T08:00:00Z', true, 500],
        ['2026-09-24T08:00:00Z', true, 300],
    ] as [$checkedAt, $isUp, $ms]) {
        UptimeCheck::create([
            'website_id' => $website->id,
            'checked_at' => Carbon::parse($checkedAt),
            'status_code' => $isUp ? 200 : 500,
            'response_time_ms' => $ms,
            'is_up' => $isUp,
        ]);
    }
    UptimeCheck::create([
        'website_id' => $other->id,
        'checked_at' => Carbon::parse('2026-09-24T09:00:00Z'),
        'status_code' => 500,
        'response_time_ms' => 5000,
        'is_up' => false,
    ]);

    // Audit: the latest one wins.
    SeoAudit::create([
        'website_id' => $website->id,
        'url' => 'https://example.es',
        'audited_at' => Carbon::parse('2026-09-01T10:00:00Z'),
        'score' => 70,
    ]);
    SeoAudit::create([
        'website_id' => $website->id,
        'url' => 'https://example.es',
        'audited_at' => Carbon::parse('2026-09-20T10:15:00Z'),
        'score' => 87,
    ]);

    // Alerts: only unresolved ones of this website count.
    foreach ([null, null, '2026-09-23T00:00:00Z'] as $resolvedAt) {
        SeoAlert::create([
            'website_id' => $website->id,
            'type' => 'uptime',
            'title' => 'Alerta',
            'message' => 'Mensaje',
            'detected_at' => Carbon::parse('2026-09-22T00:00:00Z'),
            'resolved_at' => $resolvedAt ? Carbon::parse($resolvedAt) : null,
        ]);
    }
    SeoAlert::create([
        'website_id' => $other->id,
        'type' => 'uptime',
        'title' => 'Otra',
        'message' => 'Otra',
        'detected_at' => Carbon::parse('2026-09-22T00:00:00Z'),
    ]);

    // Keywords: the best 5 current positions; unranked and 6th-best are left out.
    trackKeyword($website, 'fontanero donostia', [
        '2026-09-10T00:00:00Z' => 6,
        '2026-09-20T00:00:00Z' => 4,
    ]);
    trackKeyword($website, 'fontanero urgente', ['2026-09-20T00:00:00Z' => 1]);
    trackKeyword($website, 'desatascos', ['2026-09-20T00:00:00Z' => 12]);
    trackKeyword($website, 'calderas', ['2026-09-20T00:00:00Z' => 30]);
    trackKeyword($website, 'reformas baño', [
        '2026-09-10T00:00:00Z' => null,
        '2026-09-20T00:00:00Z' => 8,
    ]);
    trackKeyword($website, 'fontanería barata', ['2026-09-20T00:00:00Z' => 50]);
    trackKeyword($website, 'sin posición', ['2026-09-20T00:00:00Z' => null]);
    trackKeyword($other, 'otra web', ['2026-09-20T00:00:00Z' => 2]);

    $this->withToken('portal-secret')
        ->getJson(portalStatusUrl($website->id))
        ->assertOk()
        ->assertExactJson([
            'website' => [
                'id' => $website->id,
                'name' => 'Fontanería Ejemplo',
                'base_url' => 'https://example.es',
            ],
            'uptime' => [
                'is_up' => true,
                'last_checked_at' => '2026-09-24T08:00:00Z',
                'uptime_percent_30d' => 75.0,
                'avg_response_ms_30d' => 400,
            ],
            'audit' => ['score' => 87, 'audited_at' => '2026-09-20T10:15:00Z'],
            'open_alerts' => 2,
            'keywords' => [
                ['keyword' => 'fontanero urgente', 'position' => 1, 'previous_position' => null],
                ['keyword' => 'fontanero donostia', 'position' => 4, 'previous_position' => 6],
                ['keyword' => 'reformas baño', 'position' => 8, 'previous_position' => null],
                ['keyword' => 'desatascos', 'position' => 12, 'previous_position' => null],
                ['keyword' => 'calderas', 'position' => 30, 'previous_position' => null],
            ],
        ]);
});
