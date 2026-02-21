<?php

namespace App\Http\Controllers;

use App\Models\Keyword;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class KeywordController extends Controller
{
    public function store(Request $request, Website $website): RedirectResponse
    {
        $validated = $request->validate([
            'term' => ['required', 'string', 'max:255'],
            'target_url' => ['nullable', 'url', 'max:255'],
            'search_engine' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'device' => ['required', 'in:desktop,mobile'],
            'priority' => ['required', 'integer', 'min:1', 'max:3'],
        ]);

        $website->keywords()->create($validated);

        return back()->with('status', 'Keyword added.');
    }

    public function destroy(Keyword $keyword): RedirectResponse
    {
        $keyword->delete();

        return back()->with('status', 'Keyword deleted.');
    }
}
