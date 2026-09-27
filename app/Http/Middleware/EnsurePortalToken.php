<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the read-only client portal API with a shared bearer token (PORTAL_API_TOKEN).
 * An empty configured token rejects every request.
 */
class EnsurePortalToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.portal.token');
        $given = (string) $request->bearerToken();

        if ($expected === '' || ! hash_equals($expected, $given)) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return $next($request);
    }
}
