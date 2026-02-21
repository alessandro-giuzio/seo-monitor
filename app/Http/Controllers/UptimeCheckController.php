<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class UptimeCheckController extends Controller
{
    public function store(Request $request, Website $website): RedirectResponse
    {
        $validated = $request->validate([
            'checked_at' => ['required', 'date'],
            'status_code' => ['nullable', 'integer', 'min:100', 'max:599'],
            'response_time_ms' => ['nullable', 'integer', 'min:1'],
            'is_up' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $website->uptimeChecks()->create($validated);

        return back()->with('status', 'Uptime check recorded.');
    }
}
