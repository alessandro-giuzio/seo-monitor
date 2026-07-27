<?php

namespace App\Http\Controllers;

use App\Models\GscConnection;
use App\Models\Website;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class GscOAuthController extends Controller
{
    public function connect(Website $website): RedirectResponse
    {
        if (! config('services.google.client_id')) {
            return redirect()->route('gsc.index', ['website_id' => $website->id])
                ->withErrors(['gsc' => 'Google OAuth is not configured (missing GOOGLE_CLIENT_ID).']);
        }

        $state = Crypt::encryptString((string) $website->id);

        $query = http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => config('services.google.redirect'),
            'response_type' => 'code',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'scope' => 'https://www.googleapis.com/auth/webmasters.readonly',
            'state' => $state,
        ]);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?'.$query);
    }

    public function callback(Request $request): RedirectResponse
    {
        $request->validate([
            'state' => ['required', 'string'],
            'code' => ['required', 'string'],
        ]);

        try {
            $websiteId = (int) Crypt::decryptString($request->string('state')->toString());
        } catch (\Throwable) {
            return redirect()->route('gsc.index')->withErrors(['gsc' => 'Invalid or expired connection request.']);
        }

        $website = Website::findOrFail($websiteId);

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => config('services.google.redirect'),
            'code' => $request->string('code')->toString(),
            'grant_type' => 'authorization_code',
        ]);

        if (! $response->successful() || ! $response->json('refresh_token')) {
            return redirect()->route('gsc.index', ['website_id' => $website->id])
                ->withErrors(['gsc' => 'Could not complete Google authorization: '.$response->body()]);
        }

        GscConnection::updateOrCreate(
            ['website_id' => $website->id],
            [
                'access_token' => $response->json('access_token'),
                'refresh_token' => $response->json('refresh_token'),
                'token_expires_at' => now()->addSeconds((int) $response->json('expires_in', 3600)),
                'connected_at' => now(),
            ]
        );

        return redirect()->route('gsc.index', ['website_id' => $website->id])
            ->with('status', 'Google Search Console connected.');
    }

    public function disconnect(Website $website): RedirectResponse
    {
        $website->gscConnection?->delete();

        return redirect()->route('gsc.index', ['website_id' => $website->id])
            ->with('status', 'Google Search Console disconnected.');
    }
}
