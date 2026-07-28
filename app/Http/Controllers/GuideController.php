<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class GuideController extends Controller
{
    public function index(): View
    {
        $steps = [
            [
                'title' => 'Add your website',
                'desc' => 'On Websites — name and URL are required. Also worth filling in now: GSC property (needed to connect Search Console later), industry, target country, and crawl frequency.',
            ],
            [
                'title' => 'Add the keywords you care about',
                'desc' => "On that website's detail page. Term, target URL, search engine, device, priority — you'll log real ranking positions against these later.",
            ],
            [
                'title' => 'Connect Google Search Console',
                'desc' => 'On GSC — click Connect, authorize, done. It syncs daily on its own from here. This one step feeds Content Decay, the traffic-drop alert, and Reports.',
            ],
            [
                'title' => 'Run your first crawl',
                'desc' => 'On Technical — click Run crawl. Powers Link Opportunities, two alert types, most of the Checklist, and Release QA.',
            ],
            [
                'title' => 'Audit your key pages',
                'desc' => 'On Audits — one URL at a time. Title, meta description, H1, alt text, word count. Feeds the Checklist and Release QA scores.',
            ],
            [
                'title' => 'Run alert evaluation',
                'desc' => 'On Alerts — now that crawl and GSC data exist, this actually finds something. It also re-runs automatically every hour from here on.',
            ],
            [
                'title' => 'Check Reports, Release QA, Checklist',
                'desc' => 'All three now have real data to aggregate — this is where the payoff shows up.',
            ],
        ];

        $legend = [
            ['label' => 'Live fetch', 'color' => 'sky', 'desc' => 'The app reaches out to your real website right now and reads what\'s there.'],
            ['label' => 'Synced automatically', 'color' => 'emerald', 'desc' => 'Connected once, then updates itself on a schedule — no clicking required.'],
            ['label' => 'You enter it', 'color' => 'amber', 'desc' => 'Numbers you already have elsewhere (Search Console exports, Ahrefs, your own notes) — paste or type them in.'],
            ['label' => 'Read-only', 'color' => 'slate', 'desc' => 'Nothing to enter — it\'s computed from everything else.'],
        ];

        $modules = [
            ['title' => 'Dashboard', 'route' => 'dashboard', 'chips' => [['Read-only', 'slate']], 'desc' => 'Your daily landing page. KPI tiles and a health card per site, pulled from every other page. Nothing to enter here.'],
            ['title' => 'Websites', 'route' => 'websites.index', 'chips' => [['You enter it', 'amber']], 'desc' => 'Where everything starts. Add a site\'s name and URL; the rest is optional but unlocks other pages.'],
            ['title' => 'GSC', 'route' => 'gsc.index', 'chips' => [['Synced automatically', 'emerald'], ['Fallback: paste', 'amber']], 'desc' => 'Connect once via OAuth and it syncs daily, plus an on-demand Sync button. Unconnected sites can still paste CSV export rows.'],
            ['title' => 'Domain Overview', 'route' => 'domain-overview.index', 'chips' => [['You enter it', 'amber']], 'desc' => 'Log periodic domain metrics from a third-party tool — traffic, keyword count, backlinks, visibility. Plotted as a trend over time.'],
            ['title' => 'Keyword Research', 'route' => 'keyword-research.index', 'chips' => [['You enter it', 'amber']], 'desc' => 'A separate idea database from your tracked keywords. Bulk-paste CSV rows; "Track" promotes an idea into a real tracked keyword.'],
            ['title' => 'Competitors', 'route' => 'competitors.index', 'chips' => [['You enter it', 'amber'], ['Gap: auto', 'slate']], 'desc' => 'Add competitors and log their keyword positions as you find them. The Keyword Gap table computes opportunities automatically.'],
            ['title' => 'Backlinks', 'route' => 'backlinks.index', 'chips' => [['You enter it', 'amber']], 'desc' => 'Log links you\'ve already found elsewhere. No live discovery — this is a tracking log, not a crawler.'],
            ['title' => 'Technical', 'route' => 'technical.index', 'chips' => [['Live fetch', 'sky']], 'desc' => 'The real crawler — robots.txt, sitemap, up to 200 pages. Finds indexability and orphan-page issues, generates internal-link suggestions.'],
            ['title' => 'Link Opportunities', 'route' => 'links.index', 'chips' => [['Read-only', 'slate']], 'desc' => 'Auto-generated after each Technical crawl. Nothing to configure — re-run a crawl to refresh.'],
            ['title' => 'Content Decay', 'route' => 'decay.index', 'chips' => [['Read-only', 'slate']], 'desc' => 'Pages whose clicks dropped 20%+ over the last 28 days. Needs GSC data connected — empty without it.'],
            ['title' => 'Alerts', 'route' => 'alerts.index', 'chips' => [['Hourly', 'emerald'], ['+ on-demand', 'sky']], 'desc' => 'Orphan pages, non-indexable pages, and 30%+ traffic drops — detected automatically every hour, or on demand with Run evaluation.'],
            ['title' => 'Reports', 'route' => 'reports.index', 'chips' => [['Read-only', 'slate']], 'desc' => 'One-page rollup per site. Export CSV for sharing outside the app — handy for client updates.'],
            ['title' => 'Change Log', 'route' => 'change-log.index', 'chips' => [['You enter it', 'amber']], 'desc' => 'A manual record of what you changed and when. Nothing reads it automatically — it\'s there for when a ranking shift shows up later and you need to correlate it.'],
            ['title' => 'Redirects', 'route' => 'redirects.index', 'chips' => [['Rules: you enter', 'amber'], ['Check: live', 'sky']], 'desc' => 'Maintain redirect rules; the Check button live-verifies the real response matches what you configured.'],
            ['title' => 'Release QA', 'route' => 'release-qa.index', 'chips' => [['Read-only', 'slate']], 'desc' => 'A pre-deploy scorecard combining crawl health, audit score, open alerts, and redirect checks. Run before you ship changes.'],
            ['title' => 'Checklist', 'route' => 'checklist.index', 'chips' => [['Auto-graded', 'slate']], 'desc' => 'A 4-section SEO checklist graded from your latest crawl, audit, and GSC data. Generate tasks turns any fail/warn into a to-do with a due date.'],
            ['title' => 'Audits', 'route' => 'audits.index', 'chips' => [['Live fetch', 'sky']], 'desc' => 'On-page analysis for one URL — title, meta description, H1, alt text, word count, link counts. Score plus an itemized issue list.'],
            ['title' => 'Profile', 'route' => 'profile.edit', 'chips' => [['Account', 'slate']], 'desc' => 'Name, email, password, account deletion. Not SEO-related — just your login.'],
        ];

        $automation = [
            ['name' => 'Technical crawl', 'desc' => 'Crawls every website whose crawl frequency says it\'s due.', 'cadence' => 'hourly'],
            ['name' => 'Alert evaluation', 'desc' => 'Same check as the "Run evaluation" button, for every website.', 'cadence' => 'hourly'],
            ['name' => 'Search Console sync', 'desc' => 'Pulls fresh data for every connected website.', 'cadence' => 'daily · 3am'],
        ];

        return view('guide.index', compact('steps', 'legend', 'modules', 'automation'));
    }
}
