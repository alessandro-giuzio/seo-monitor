<?php

use App\Models\Competitor;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Regression test for the /competitors 500 (production incident)
|--------------------------------------------------------------------------
|
| CompetitorController@index eager-loads 'competitors.keywordSnapshots' with
| a constraint closure type-hinted as Illuminate\Database\Eloquent\Builder.
| Laravel actually passes the relation instance (HasMany) to nested
| eager-load constraint closures, not a Builder, so PHP threw a TypeError
| the moment a website had at least one competitor. A website with zero
| competitors never invoked the closure, so this was invisible until real
| data existed — exactly what happened after adding a competitor on
| production.
*/

test('competitors page renders once a website has a competitor', function () {
    $user = User::factory()->create();
    $website = Website::factory()->create();
    Competitor::factory()->for($website)->create();

    $this->actingAs($user)
        ->get(route('competitors.index', ['website_id' => $website->id]))
        ->assertOk();
});

test('adding a competitor redirects back to a working competitors page', function () {
    $user = User::factory()->create();
    $website = Website::factory()->create();

    $this->actingAs($user)
        ->post(route('competitors.store', $website), [
            'name' => 'Rival Co',
            'domain' => 'rival.example',
        ])
        ->assertRedirect(route('competitors.index', ['website_id' => $website->id]));

    $this->actingAs($user)
        ->get(route('competitors.index', ['website_id' => $website->id]))
        ->assertOk();
});
