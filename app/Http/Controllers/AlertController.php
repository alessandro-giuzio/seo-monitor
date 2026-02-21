<?php

namespace App\Http\Controllers;

use App\Models\SeoAlert;
use App\Models\Website;
use App\Services\SeoAlertService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AlertController extends Controller
{
    public function index(Request $request): View
    {
        $query = SeoAlert::query()->with('website')->latest('detected_at');
        if ($request->filled('website_id')) {
            $query->where('website_id', $request->integer('website_id'));
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->string('severity')->toString());
        }
        if ($request->boolean('open_only')) {
            $query->whereNull('resolved_at');
        }

        return view('alerts.index', [
            'alerts' => $query->paginate(50)->withQueryString(),
            'websites' => Website::query()->orderBy('name')->get(),
        ]);
    }

    public function evaluate(SeoAlertService $alertService): RedirectResponse
    {
        $created = 0;
        $websites = Website::query()->get();
        foreach ($websites as $website) {
            $created += $alertService->evaluateForWebsite($website);
        }

        return back()->with('status', "{$created} alert(s) created.");
    }

    public function resolve(SeoAlert $alert): RedirectResponse
    {
        $alert->update(['resolved_at' => now()]);

        return back()->with('status', 'Alert resolved.');
    }
}
