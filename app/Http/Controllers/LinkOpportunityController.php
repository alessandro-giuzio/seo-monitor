<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LinkOpportunityController extends Controller
{
    public function index(Request $request): View
    {
        $websites = Website::query()->orderBy('name')->get();
        $selectedWebsite = null;
        $opportunities = collect();

        if ($websites->isNotEmpty()) {
            $selectedId = $request->integer('website_id') ?: $websites->first()->id;
            $selectedWebsite = Website::find($selectedId);
            if ($selectedWebsite) {
                $opportunities = $selectedWebsite->internalLinkOpportunities()
                    ->latest()
                    ->paginate(100)
                    ->withQueryString();
            }
        }

        return view('link-opportunities.index', [
            'websites' => $websites,
            'selectedWebsite' => $selectedWebsite,
            'opportunities' => $opportunities,
        ]);
    }
}
