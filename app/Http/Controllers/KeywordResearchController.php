<?php

namespace App\Http\Controllers;

use App\Models\Keyword;
use App\Models\KeywordIdea;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KeywordResearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = KeywordIdea::query()->with('website')->latest();

        if ($request->filled('website_id')) {
            $query->where('website_id', $request->integer('website_id'));
        }

        if ($request->filled('q')) {
            $query->where('keyword', 'like', '%'.$request->string('q')->toString().'%');
        }

        if ($request->filled('country')) {
            $query->where('country', strtoupper($request->string('country')->toString()));
        }

        if ($request->filled('intent')) {
            $query->where('intent', $request->string('intent')->toString());
        }

        if ($request->filled('min_volume')) {
            $query->where('search_volume', '>=', (int) $request->input('min_volume'));
        }

        if ($request->filled('max_kd')) {
            $query->where('keyword_difficulty', '<=', (int) $request->input('max_kd'));
        }

        $ideas = $query->paginate(50)->withQueryString();

        return view('keyword-research.index', [
            'ideas' => $ideas,
            'websites' => Website::query()->orderBy('name')->get(),
            'filters' => $request->only(['website_id', 'q', 'country', 'intent', 'min_volume', 'max_kd']),
            'stats' => [
                'total_ideas' => KeywordIdea::count(),
                'low_kd_high_volume' => KeywordIdea::query()
                    ->where('keyword_difficulty', '<=', 29)
                    ->where('search_volume', '>=', 500)
                    ->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'website_id' => ['nullable', 'exists:websites,id'],
            'seed_keyword' => ['nullable', 'string', 'max:255'],
            'keyword' => ['required', 'string', 'max:255'],
            'search_volume' => ['nullable', 'integer', 'min:0'],
            'keyword_difficulty' => ['nullable', 'integer', 'min:0', 'max:100'],
            'cpc' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'intent' => ['nullable', 'in:informational,navigational,commercial,transactional,mixed'],
            'country' => ['nullable', 'string', 'size:2'],
        ]);

        if (! empty($validated['country'])) {
            $validated['country'] = strtoupper($validated['country']);
        }

        KeywordIdea::create($validated);

        return back()->with('status', 'Keyword idea added.');
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'website_id' => ['nullable', 'exists:websites,id'],
            'seed_keyword' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'size:2'],
            'rows' => ['required', 'string'],
        ]);

        $websiteId = $validated['website_id'] ?? null;
        $seedKeyword = $validated['seed_keyword'] ?? null;
        $country = isset($validated['country']) ? strtoupper($validated['country']) : null;
        $created = 0;

        $lines = preg_split('/\r\n|\r|\n/', trim($validated['rows'])) ?: [];
        foreach ($lines as $line) {
            if (blank($line)) {
                continue;
            }

            $parts = array_map('trim', explode(',', $line));
            $keyword = $parts[0] ?? null;
            if (! $keyword) {
                continue;
            }

            KeywordIdea::create([
                'website_id' => $websiteId,
                'seed_keyword' => $seedKeyword,
                'country' => $country,
                'keyword' => $keyword,
                'search_volume' => isset($parts[1]) && is_numeric($parts[1]) ? (int) $parts[1] : null,
                'keyword_difficulty' => isset($parts[2]) && is_numeric($parts[2]) ? (int) $parts[2] : null,
                'cpc' => isset($parts[3]) && is_numeric($parts[3]) ? (float) $parts[3] : null,
                'intent' => $parts[4] ?? null,
            ]);
            $created++;
        }

        return back()->with('status', "{$created} keyword idea(s) imported.");
    }

    public function track(KeywordIdea $idea): RedirectResponse
    {
        if (! $idea->website_id) {
            return back()->withErrors(['idea' => 'Attach this idea to a website first.']);
        }

        $existing = Keyword::query()
            ->where('website_id', $idea->website_id)
            ->where('term', $idea->keyword)
            ->first();

        if ($existing) {
            return back()->with('status', 'Keyword already tracked.');
        }

        Keyword::create([
            'website_id' => $idea->website_id,
            'term' => $idea->keyword,
            'target_url' => null,
            'search_engine' => 'Google',
            'location' => $idea->country,
            'device' => 'desktop',
            'priority' => 2,
        ]);

        return back()->with('status', 'Idea promoted to tracked keyword.');
    }
}
