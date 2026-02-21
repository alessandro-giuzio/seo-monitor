<?php

namespace App\Http\Controllers;

use App\Models\RedirectRule;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class RedirectManagerController extends Controller
{
    public function index(Request $request): View
    {
        $websites = Website::query()->orderBy('name')->get();
        $selectedWebsite = null;
        $rules = collect();

        if ($websites->isNotEmpty()) {
            $selectedId = $request->integer('website_id') ?: $websites->first()->id;
            $selectedWebsite = Website::find($selectedId);
            if ($selectedWebsite) {
                $rules = $selectedWebsite->redirectRules()->paginate(100)->withQueryString();
            }
        }

        return view('redirects.index', [
            'websites' => $websites,
            'selectedWebsite' => $selectedWebsite,
            'rules' => $rules,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'website_id' => ['required', 'exists:websites,id'],
            'from_path' => ['required', 'string', 'max:255'],
            'to_url' => ['required', 'url', 'max:2048'],
            'status_code' => ['required', 'integer', 'in:301,302,307,308'],
            'is_active' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['from_path'] = '/'.ltrim($validated['from_path'], '/');

        RedirectRule::updateOrCreate(
            [
                'website_id' => $validated['website_id'],
                'from_path' => $validated['from_path'],
            ],
            [
                'to_url' => $validated['to_url'],
                'status_code' => $validated['status_code'],
                'is_active' => $validated['is_active'],
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return redirect()
            ->route('redirects.index', ['website_id' => $validated['website_id']])
            ->with('status', 'Redirect rule saved.');
    }

    public function check(RedirectRule $rule): RedirectResponse
    {
        $website = Website::findOrFail($rule->website_id);
        $fromUrl = rtrim($website->base_url, '/').$rule->from_path;

        $result = 'ok';
        $status = null;

        try {
            $response = Http::timeout(15)->withoutRedirecting()->get($fromUrl);
            $status = $response->status();
            $location = $response->header('Location');

            if ($status < 300 || $status >= 400) {
                $result = 'expected redirect but got non-3xx response';
            } elseif ($location === null) {
                $result = 'missing location header';
            } elseif (! str_starts_with($location, $rule->to_url)) {
                $result = 'location mismatch';
            }
        } catch (\Throwable $e) {
            $result = 'request failed: '.$e->getMessage();
        }

        $rule->update([
            'last_checked_at' => now(),
            'last_status_code' => $status,
            'last_check_result' => $result,
        ]);

        return redirect()
            ->route('redirects.index', ['website_id' => $rule->website_id])
            ->with('status', 'Redirect check completed.');
    }

    public function destroy(RedirectRule $rule): RedirectResponse
    {
        $websiteId = $rule->website_id;
        $rule->delete();

        return redirect()
            ->route('redirects.index', ['website_id' => $websiteId])
            ->with('status', 'Redirect rule deleted.');
    }
}
