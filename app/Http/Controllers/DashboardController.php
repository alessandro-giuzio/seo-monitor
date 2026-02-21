<?php

namespace App\Http\Controllers;

use App\Models\Keyword;
use App\Models\KeywordIdea;
use App\Models\RankingSnapshot;
use App\Models\SeoAudit;
use App\Models\SeoAlert;
use App\Models\UptimeCheck;
use App\Models\Website;
use App\Models\Backlink;
use App\Models\Competitor;
use App\Models\GscMetric;
use App\Models\CrawlPage;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $websites = Website::query()
            ->with([
                'keywords.latestSnapshot',
                'latestSeoAudit',
                'latestUptimeCheck',
            ])
            ->orderBy('name')
            ->get();

        $latestSnapshotIds = RankingSnapshot::query()
            ->select(DB::raw('MAX(id) as id'))
            ->groupBy('keyword_id');

        $topTenKeywords = RankingSnapshot::query()
            ->whereIn('id', $latestSnapshotIds)
            ->whereNotNull('position')
            ->where('position', '<=', 10)
            ->count();

        $recentAudits = SeoAudit::query()
            ->with('website')
            ->latest('audited_at')
            ->limit(10)
            ->get();

        $recentUptimeChecks = UptimeCheck::query()
            ->with('website')
            ->latest('checked_at')
            ->limit(10)
            ->get();

        $uptimeChecksCount = UptimeCheck::count();
        $uptimeHealthyCount = UptimeCheck::where('is_up', true)->count();

        return view('dashboard', [
            'websites' => $websites,
            'stats' => [
                'sites' => Website::count(),
                'keywords' => Keyword::count(),
                'top_ten_keywords' => $topTenKeywords,
                'audit_count' => SeoAudit::count(),
                'keyword_ideas' => KeywordIdea::count(),
                'competitors' => Competitor::count(),
                'backlinks' => Backlink::count(),
                'gsc_rows' => GscMetric::count(),
                'crawl_pages' => CrawlPage::count(),
                'open_alerts' => SeoAlert::whereNull('resolved_at')->count(),
                'uptime_rate' => $uptimeChecksCount > 0
                    ? round(($uptimeHealthyCount / $uptimeChecksCount) * 100, 1)
                    : null,
            ],
            'recentAudits' => $recentAudits,
            'recentUptimeChecks' => $recentUptimeChecks,
        ]);
    }
}
