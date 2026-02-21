<?php

namespace App\Http\Controllers;

use App\Models\Keyword;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class RankingSnapshotController extends Controller
{
    public function store(Request $request, Keyword $keyword): RedirectResponse
    {
        $validated = $request->validate([
            'checked_at' => ['required', 'date'],
            'position' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'search_volume' => ['nullable', 'integer', 'min:0'],
            'difficulty' => ['nullable', 'integer', 'min:0', 'max:100'],
            'serp_features' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $features = null;
        if (!empty($validated['serp_features'])) {
            $features = array_values(array_filter(array_map('trim', explode(',', $validated['serp_features']))));
        }

        $keyword->rankingSnapshots()->create([
            'checked_at' => $validated['checked_at'],
            'position' => $validated['position'] ?? null,
            'search_volume' => $validated['search_volume'] ?? null,
            'difficulty' => $validated['difficulty'] ?? null,
            'serp_features' => $features,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('status', 'Ranking snapshot logged.');
    }
}
