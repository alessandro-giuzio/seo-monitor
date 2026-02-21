<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    public function index(): View
    {
        $websites = Website::query()->orderBy('name')->paginate(20);

        return view('websites.index', ['websites' => $websites]);
    }

    public function show(Website $website): View
    {
        $website->load([
            'keywords.latestSnapshot',
            'uptimeChecks' => fn ($query) => $query->latest('checked_at')->limit(20),
            'seoAudits' => fn ($query) => $query->latest('audited_at')->limit(20),
            'domainMetricsSnapshots' => fn ($query) => $query->latest('snapshot_at')->limit(12),
            'competitors.keywordSnapshots' => fn ($query) => $query->latest('checked_at')->limit(10),
            'backlinks' => fn ($query) => $query->latest('last_seen_at')->limit(20),
            'crawlRuns' => fn ($query) => $query->latest('started_at')->limit(5),
            'seoAlerts' => fn ($query) => $query->whereNull('resolved_at')->latest('detected_at')->limit(10),
        ]);

        return view('websites.show', ['website' => $website]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'base_url' => ['required', 'url', 'max:255', 'unique:websites,base_url'],
            'gsc_property' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'target_country' => ['nullable', 'string', 'size:2'],
            'alert_email' => ['nullable', 'email', 'max:255'],
            'crawl_frequency_hours' => ['nullable', 'integer', 'min:1', 'max:168'],
            'notes' => ['nullable', 'string', 'max:4000'],
        ]);

        $validated['target_country'] = $validated['target_country'] ?? null;
        if ($validated['target_country']) {
            $validated['target_country'] = strtoupper($validated['target_country']);
        }
        $validated['crawl_frequency_hours'] = (int) ($validated['crawl_frequency_hours'] ?? 24);
        $validated['next_crawl_at'] = now()->addHours($validated['crawl_frequency_hours']);

        Website::create($validated);

        return redirect()->route('dashboard')->with('status', 'Website added.');
    }

    public function update(Request $request, Website $website): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'base_url' => ['required', 'url', 'max:255', Rule::unique('websites', 'base_url')->ignore($website->id)],
            'gsc_property' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'target_country' => ['nullable', 'string', 'size:2'],
            'alert_email' => ['nullable', 'email', 'max:255'],
            'crawl_frequency_hours' => ['nullable', 'integer', 'min:1', 'max:168'],
            'notes' => ['nullable', 'string', 'max:4000'],
        ]);

        $validated['target_country'] = $validated['target_country'] ?? null;
        if ($validated['target_country']) {
            $validated['target_country'] = strtoupper($validated['target_country']);
        }
        $validated['crawl_frequency_hours'] = (int) ($validated['crawl_frequency_hours'] ?? $website->crawl_frequency_hours ?? 24);

        $website->update($validated);

        return redirect()->route('websites.show', $website)->with('status', 'Website updated.');
    }

    public function destroy(Website $website): RedirectResponse
    {
        $website->delete();

        return redirect()->route('dashboard')->with('status', 'Website deleted.');
    }
}
