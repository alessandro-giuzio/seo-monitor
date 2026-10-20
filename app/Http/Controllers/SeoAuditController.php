<?php

namespace App\Http\Controllers;

use App\Models\SeoAudit;
use App\Models\Website;
use App\Services\SeoAuditService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use RuntimeException;

class SeoAuditController extends Controller
{
    public function index(): View
    {
        $audits = SeoAudit::query()
            ->with('website')
            ->latest('audited_at')
            ->paginate(25);

        $websites = Website::query()->orderBy('name')->get(['id', 'name']);

        return view('audits.index', ['audits' => $audits, 'websites' => $websites]);
    }

    public function show(SeoAudit $audit): View
    {
        $audit->load('website');

        return view('audits.show', ['audit' => $audit]);
    }

    public function store(Request $request, SeoAuditService $audits): RedirectResponse
    {
        $validated = $request->validate([
            'website_id' => ['nullable', 'exists:websites,id'],
            'url' => ['required', 'url', 'max:2048'],
            'raw_html' => ['nullable', 'string'],
            'audited_at' => ['nullable', 'date'],
        ]);

        try {
            $audit = $audits->audit(
                isset($validated['website_id']) ? Website::find($validated['website_id']) : null,
                $validated['url'],
                $validated['raw_html'] ?? null,
                $validated['audited_at'] ?? null,
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['url' => $e->getMessage()])->withInput();
        }

        return redirect()->route('audits.show', $audit)->with('status', 'Audit completed.');
    }
}
