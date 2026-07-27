<?php

use App\Models\GscMetric;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Regression tests for the GROUP BY / inherited ORDER BY bug
|--------------------------------------------------------------------------
|
| Website::gscMetrics() carries a default orderByDesc('metric_date'). Any
| aggregate query built on it (groupBy + SUM) must call ->reorder() first,
| or the inherited ORDER BY survives into the SQL and PostgreSQL rejects it
| with error 42803 (this already broke /reports, /content-decay, and the
| alerts "Run evaluation" action in production — see PLAN.md). SQLite
| tolerates the bad SQL silently, so an HTTP 200 assertion alone would not
| catch a regression here — these tests capture the actual executed SQL via
| DB::listen() and assert no GROUP BY query also contains ORDER BY.
*/

test('reports page group-by queries do not carry an inherited ORDER BY', function () {
    $user = User::factory()->create();
    $website = Website::factory()->create();
    GscMetric::factory()->count(10)->for($website)->create();

    $queries = [];
    DB::listen(function ($query) use (&$queries) {
        $queries[] = $query->sql;
    });

    $this->actingAs($user)->get(route('reports.index', ['website_id' => $website->id]))->assertOk();

    $groupByQueries = collect($queries)->filter(fn ($sql) => str_contains(strtolower($sql), 'group by'));

    expect($groupByQueries)->not->toBeEmpty();
    foreach ($groupByQueries as $sql) {
        expect(strtolower($sql))->not->toContain('order by');
    }
});

test('content decay page group-by queries do not carry an inherited ORDER BY', function () {
    $user = User::factory()->create();
    $website = Website::factory()->create();
    GscMetric::factory()->count(10)->for($website)->create();

    $queries = [];
    DB::listen(function ($query) use (&$queries) {
        $queries[] = $query->sql;
    });

    $this->actingAs($user)->get(route('decay.index', ['website_id' => $website->id]))->assertOk();

    $groupByQueries = collect($queries)->filter(fn ($sql) => str_contains(strtolower($sql), 'group by'));

    expect($groupByQueries)->not->toBeEmpty();
    foreach ($groupByQueries as $sql) {
        expect(strtolower($sql))->not->toContain('order by');
    }
});

test('alert evaluation group-by queries do not carry an inherited ORDER BY', function () {
    $user = User::factory()->create();
    $website = Website::factory()->create();
    GscMetric::factory()->count(10)->for($website)->create();

    $queries = [];
    DB::listen(function ($query) use (&$queries) {
        $queries[] = $query->sql;
    });

    $this->actingAs($user)->post(route('alerts.evaluate'))->assertRedirect();

    $groupByQueries = collect($queries)->filter(fn ($sql) => str_contains(strtolower($sql), 'group by'));

    expect($groupByQueries)->not->toBeEmpty();
    foreach ($groupByQueries as $sql) {
        expect(strtolower($sql))->not->toContain('order by');
    }
});
