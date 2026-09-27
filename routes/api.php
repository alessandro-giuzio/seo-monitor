<?php

use App\Http\Controllers\Api\PortalStatusController;
use App\Http\Middleware\EnsurePortalToken;
use Illuminate\Support\Facades\Route;

// The website id is a plain integer (no implicit model binding), so the token middleware
// always runs before the website lookup and an unauthenticated caller only ever sees 401.
Route::middleware(EnsurePortalToken::class)
    ->get('/portal/websites/{website}/status', PortalStatusController::class)
    ->whereNumber('website');
