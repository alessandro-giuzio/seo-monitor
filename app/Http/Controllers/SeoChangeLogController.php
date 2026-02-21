<?php

namespace App\Http\Controllers;

use App\Models\SeoChangeLog;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SeoChangeLogController extends Controller
{
    public function index(Request $request): View
    {
        $websites = Website::query()->orderBy('name')->get();
        $selectedWebsite = null;
        $logs = collect();

        if ($websites->isNotEmpty()) {
            $selectedId = $request->integer('website_id') ?: $websites->first()->id;
            $selectedWebsite = Website::find($selectedId);
            if ($selectedWebsite) {
                $logs = $selectedWebsite->seoChangeLogs()->paginate(50)->withQueryString();
            }
        }

        return view('change-log.index', [
            'websites' => $websites,
            'selectedWebsite' => $selectedWebsite,
            'logs' => $logs,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'website_id' => ['required', 'exists:websites,id'],
            'changed_at' => ['required', 'date'],
            'area' => ['required', 'in:content,metadata,technical,internal-links,redirects,schema,other'],
            'title' => ['required', 'string', 'max:255'],
            'old_value' => ['nullable', 'string', 'max:4000'],
            'new_value' => ['nullable', 'string', 'max:4000'],
            'impact_level' => ['required', 'in:low,medium,high'],
            'notes' => ['nullable', 'string', 'max:4000'],
        ]);

        SeoChangeLog::create($validated);

        return redirect()
            ->route('change-log.index', ['website_id' => $validated['website_id']])
            ->with('status', 'Change logged.');
    }

    public function destroy(SeoChangeLog $log): RedirectResponse
    {
        $websiteId = $log->website_id;
        $log->delete();

        return redirect()
            ->route('change-log.index', ['website_id' => $websiteId])
            ->with('status', 'Change log entry deleted.');
    }
}
