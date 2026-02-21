<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ContentDecayController extends Controller
{
    public function index(Request $request): View
    {
        $websites = Website::query()->orderBy('name')->get();
        $selectedWebsite = null;
        $rows = collect();

        if ($websites->isNotEmpty()) {
            $selectedId = $request->integer('website_id') ?: $websites->first()->id;
            $selectedWebsite = Website::find($selectedId);
            if ($selectedWebsite) {
                $today = now()->toDateString();
                $last28Start = now()->subDays(27)->toDateString();
                $prev28Start = now()->subDays(55)->toDateString();
                $prev28End = now()->subDays(28)->toDateString();

                $lastPeriod = $selectedWebsite->gscMetrics()
                    ->whereBetween('metric_date', [$last28Start, $today])
                    ->whereNotNull('page_url')
                    ->select('page_url', DB::raw('SUM(clicks) as clicks'))
                    ->groupBy('page_url')
                    ->pluck('clicks', 'page_url');

                $prevPeriod = $selectedWebsite->gscMetrics()
                    ->whereBetween('metric_date', [$prev28Start, $prev28End])
                    ->whereNotNull('page_url')
                    ->select('page_url', DB::raw('SUM(clicks) as clicks'))
                    ->groupBy('page_url')
                    ->pluck('clicks', 'page_url');

                $compiled = [];
                foreach ($prevPeriod as $url => $prevClicks) {
                    if ((int) $prevClicks < 20) {
                        continue;
                    }
                    $lastClicks = (int) ($lastPeriod[$url] ?? 0);
                    $dropPercent = round((($prevClicks - $lastClicks) / $prevClicks) * 100, 1);
                    if ($dropPercent >= 20) {
                        $compiled[] = [
                            'url' => $url,
                            'previous_clicks' => (int) $prevClicks,
                            'last_clicks' => $lastClicks,
                            'drop_percent' => $dropPercent,
                        ];
                    }
                }

                $rows = collect($compiled)->sortByDesc('drop_percent')->values();
            }
        }

        return view('decay.index', [
            'websites' => $websites,
            'selectedWebsite' => $selectedWebsite,
            'rows' => $rows,
        ]);
    }
}
