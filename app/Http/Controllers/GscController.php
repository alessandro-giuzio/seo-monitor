<?php

namespace App\Http\Controllers;

use App\Models\GscMetric;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GscController extends Controller
{
    public function index(Request $request): View
    {
        $websites = Website::query()->orderBy('name')->get();
        $selectedWebsite = null;
        $metrics = collect();

        if ($websites->isNotEmpty()) {
            $selectedId = $request->integer('website_id') ?: $websites->first()->id;
            $selectedWebsite = Website::query()->find($selectedId);
            if ($selectedWebsite) {
                $metrics = $selectedWebsite->gscMetrics()->latest('metric_date')->paginate(50)->withQueryString();
            }
        }

        return view('gsc.index', [
            'websites' => $websites,
            'selectedWebsite' => $selectedWebsite,
            'metrics' => $metrics,
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'website_id' => ['required', 'exists:websites,id'],
            'rows' => ['required', 'string'],
        ]);

        $website = Website::findOrFail($validated['website_id']);
        $lines = preg_split('/\r\n|\r|\n/', trim($validated['rows'])) ?: [];
        $created = 0;

        foreach ($lines as $line) {
            if (blank($line)) {
                continue;
            }

            $cols = array_map('trim', str_getcsv($line));
            if (count($cols) < 5) {
                continue;
            }

            $metricDate = $cols[0];
            if (! strtotime($metricDate)) {
                continue;
            }

            $isChartExport = count($cols) === 5;

            if ($isChartExport) {
                // Search Console chart export: Date,Clicks,Impressions,CTR,Position
                $query = null;
                $pageUrl = $website->base_url;
                $clicks = is_numeric($cols[1]) ? (int) $cols[1] : 0;
                $impressions = is_numeric($cols[2]) ? (int) $cols[2] : 0;
                $ctrRaw = $cols[3];
                $positionRaw = $cols[4] ?? null;
            } else {
                // Detailed export: date,query,page_url,clicks,impressions,ctr,avg_position
                $query = $cols[1] !== '' ? $cols[1] : null;
                $pageUrl = $cols[2] !== '' ? $cols[2] : null;
                $clicks = is_numeric($cols[3]) ? (int) $cols[3] : 0;
                $impressions = is_numeric($cols[4]) ? (int) $cols[4] : 0;
                $ctrRaw = $cols[5] ?? '';
                $positionRaw = $cols[6] ?? null;
            }

            $ctr = null;
            $normalizedCtr = str_replace('%', '', $ctrRaw);
            if (is_numeric($normalizedCtr)) {
                $ctrFloat = (float) $normalizedCtr;
                $ctr = str_contains((string) $ctrRaw, '%') ? $ctrFloat / 100 : $ctrFloat;
            } elseif ($impressions > 0) {
                $ctr = $clicks / $impressions;
            }

            $position = is_numeric((string) $positionRaw) ? (float) $positionRaw : null;

            GscMetric::create([
                'website_id' => $website->id,
                'metric_date' => $metricDate,
                'query' => $query,
                'page_url' => $pageUrl,
                'clicks' => $clicks,
                'impressions' => $impressions,
                'ctr' => $ctr ?? 0,
                'avg_position' => $position,
            ]);
            $created++;
        }

        return redirect()
            ->route('gsc.index', ['website_id' => $website->id])
            ->with('status', "{$created} GSC row(s) imported.");
    }
}
