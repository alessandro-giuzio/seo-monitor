<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests can read the privacy policy without logging in', function () {
    $this->get('/privacy')
        ->assertOk()
        ->assertSee('Privacy Policy')
        ->assertSee('Agency-GA')
        ->assertSee('https://developers.google.com/terms/api-services-user-data-policy', false);
});

test('the login page links to the privacy policy', function () {
    $this->get(route('login'))->assertOk()->assertSee(route('privacy'), false);
});
