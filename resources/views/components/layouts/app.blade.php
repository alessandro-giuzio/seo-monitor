<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'SEO Toolkit' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
<div class="bg-[radial-gradient(circle_at_top,_rgba(14,165,233,0.20),_transparent_45%),radial-gradient(circle_at_80%_20%,_rgba(249,115,22,0.18),_transparent_35%)]">
    <header class="relative z-50 border-b border-slate-800/70 bg-slate-950/80 backdrop-blur">
        <nav class="mx-auto flex max-w-7xl items-start justify-between gap-8 px-6 py-4 lg:items-center">
            <div class="shrink-0">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <x-application-logo class="h-9 w-9" />
                    <div>
                        <span class="block text-base font-semibold tracking-tight text-slate-100">SEO Toolkit</span>
                        <span class="block text-xs text-slate-400">Research + Monitoring</span>
                    </div>
                </a>
            </div>
            @php
                $navLinks = [
                    ['dashboard', 'dashboard', 'Dashboard'],
                    ['websites.index', 'websites.*', 'Websites'],
                    ['gsc.index', 'gsc.*', 'GSC'],
                    ['domain-overview.index', 'domain-overview.*', 'Domain'],
                    ['keyword-research.index', 'keyword-research.*', 'Keywords'],
                    ['competitors.index', 'competitors.*', 'Gap'],
                    ['backlinks.index', 'backlinks.*', 'Backlinks'],
                    ['technical.index', 'technical.*', 'Technical'],
                    ['decay.index', 'decay.*', 'Decay'],
                    ['links.index', 'links.*', 'Link Ops'],
                    ['alerts.index', 'alerts.*', 'Alerts'],
                    ['reports.index', 'reports.*', 'Reports'],
                    ['change-log.index', 'change-log.*', 'Change Log'],
                    ['redirects.index', 'redirects.*', 'Redirects'],
                    ['release-qa.index', 'release-qa.*', 'Release QA'],
                    ['checklist.index', 'checklist.*', 'Checklist'],
                    ['audits.index', 'audits.*', 'Audits'],
                    ['guide.index', 'guide.*', 'Guide'],
                ];
            @endphp
            <div class="flex flex-1 flex-wrap items-center justify-start gap-2 pt-1 text-sm lg:justify-end lg:pt-0">
                @foreach ($navLinks as [$route, $pattern, $label])
                    <a href="{{ route($route) }}"
                       @class([
                           'rounded-md border px-3 py-1.5',
                           'border-sky-400 text-sky-300 bg-sky-500/10' => request()->routeIs($pattern),
                           'border-slate-700 hover:border-sky-400 hover:text-sky-300' => ! request()->routeIs($pattern),
                       ])>{{ $label }}</a>
                @endforeach

                {{-- User menu --}}
                <div x-data="{ open: false }" class="relative ml-2">
                    <button @click="open = !open" class="flex items-center gap-1.5 rounded-md border border-slate-700 px-3 py-1.5 text-sm hover:border-sky-400 hover:text-sky-300">
                        {{ Auth::user()->name }}
                        <svg class="h-3 w-3 opacity-60" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-transition
                         class="absolute right-0 top-full z-50 mt-1 w-40 rounded-lg border border-slate-700 bg-slate-900 py-1 shadow-lg">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-slate-800">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 text-left text-sm text-red-400 hover:bg-slate-800">
                                Log out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <main class="mx-auto max-w-7xl px-6 py-8">
        @php
            $routeName = request()->route()?->getName();
            $helpByRoute = [
                'dashboard' => [
                    'Read-only overview — nothing is entered here. KPI tiles and the health grid pull from every other page.',
                    'New here? Add a website first, then work through Websites → GSC → Technical → Audits in order — most other pages depend on that data existing.',
                    'The "Open Alerts" tile and uptime % update automatically once you run alert evaluation and log uptime checks elsewhere.',
                ],
                'websites.index' => [
                    'Only Name and Base URL are required to add a site — GSC property, industry, target country, alert email, and crawl frequency are optional but unlock other features.',
                    'Crawl frequency (hours) controls when the hourly scheduler decides this site is "due" for an automatic Technical crawl.',
                    'Use the "⋯" menu on a card to rename/change the URL or delete a site without opening it.',
                ],
                'websites.show' => [
                    'This is mission control for one site — settings, latest status snapshots, tracked keywords, and uptime history all live here.',
                    'Add keywords under "Keywords", then use "Log ranking" on each row to record a real SERP position you observed — this app does not fetch rankings automatically.',
                    'There is no automatic uptime pinger — use "Record check" to log an up/down observation yourself (or paste results from an external monitor).',
                ],
                'gsc.index' => [
                    'Paste rows from a real Google Search Console export — no live API connection exists, this is manual import only.',
                    'Two formats auto-detected by column count: 5 columns = date,clicks,impressions,ctr,position (chart export). 7 columns = date,query,page_url,clicks,impressions,ctr,avg_position (page export).',
                    'This is the single most important data source in the app — it powers Content Decay, the traffic-drop alert type, and part of Reports. Import regularly.',
                ],
                'domain-overview.index' => [
                    'Log domain-level metrics from a third-party tool (Ahrefs, Semrush, etc.) — traffic estimate, organic keywords, referring domains, backlink count, visibility index, average position.',
                    'Each submission is a dated snapshot; log periodically (e.g. weekly/monthly) to build a trend, not just once.',
                ],
                'keyword-research.index' => [
                    'A separate idea database from your tracked keywords — use it to research before committing to tracking something.',
                    'Bulk import format: keyword,volume,kd,cpc,intent — one per line.',
                    '"Track" promotes an idea into a real tracked Keyword on its website. Log its actual ranking position afterward from the website detail page.',
                ],
                'competitors.index' => [
                    'Add a competitor by name/domain, then log their keyword ranking snapshots as you find them (no live rank-tracking API — manual entry).',
                    'The Keyword Gap table below is fully automatic: it surfaces keywords where a competitor ranks top 30 and you rank worse (or not at all), ranked by opportunity.',
                ],
                'backlinks.index' => [
                    'Log backlinks you already found elsewhere — no live backlink-discovery integration.',
                    'Flag toxic or nofollow links as you enter them so the filters and stats tiles (total, toxic, nofollow, avg authority) stay useful.',
                ],
                'technical.index' => [
                    '"Run crawl" makes real live HTTP requests to your site — fetches robots.txt and sitemap.xml, then crawls up to N pages (default 30, max 200).',
                    'Also runs automatically every hour for any site whose crawl frequency says it\'s due — this button is for on-demand runs.',
                    'Crawl output feeds three other pages: Link Opportunities (auto-generated), the orphan/indexation Alert types, and most of the Checklist and Release QA scores.',
                    'If a site is unreachable, that run is marked failed with the error recorded — it won\'t block crawling your other sites.',
                ],
                'technical.runs.show' => [
                    'One crawl run in full detail: every page checked, plus the internal link suggestions generated from this run.',
                    'Orphan pages (zero internal links pointing to them) and non-indexable pages are what drive Alerts and Release QA — start fixes there.',
                ],
                'decay.index' => [
                    'Requires GSC data — empty until you\'ve imported rows on the GSC page.',
                    'Flags pages where clicks dropped 20%+ comparing the last 28 days to the prior 28, filtering out low-traffic noise (needs 20+ prior clicks to qualify).',
                    'Prioritize the biggest drops for a content refresh or additional internal links.',
                ],
                'links.index' => [
                    'Fully automatic, read-only output — generated the moment you run a Technical crawl. Nothing to configure here.',
                    'Suggests linking FROM well-linked, high-content-depth pages TO strong pages that currently get little internal linking.',
                    'Re-run a Technical crawl to refresh these suggestions.',
                ],
                'alerts.index' => [
                    '"Run evaluation" checks every website for orphan pages, non-indexable pages (both from the latest Technical crawl), and traffic drops of 30%+ (from GSC data).',
                    'This also runs automatically every hour in the background — the button is only for checking on-demand.',
                    'Won\'t create a duplicate for the same issue within 24 hours, so it\'s safe to run repeatedly.',
                ],
                'reports.index' => [
                    'A one-page rollup per website: tracked keywords, top-10 count, open alerts, 30-day uptime, latest crawl health, and top content-decay pages.',
                    '"Export CSV" downloads the same numbers for sharing with a client or team outside the app.',
                    'Quality depends on the pages behind it — import GSC data and run a crawl first for a meaningful report.',
                ],
                'change-log.index' => [
                    'A manual audit trail — log what changed (area, old/new value, impact level) and when.',
                    'Nothing else in the app reads this automatically, but it\'s essential later for correlating a ranking or traffic shift with what you actually changed.',
                ],
                'redirects.index' => [
                    'Maintain your redirect map (from-path → destination URL, status code, active flag).',
                    '"Check" makes a real live request to your site to verify the redirect actually works and points where you expect — run this before releases.',
                    'Rules currently failing their last check feed directly into the Release QA score.',
                ],
                'release-qa.index' => [
                    'A pre-deploy gate: scores a website against latest crawl health, latest audit score, open alerts, and failing redirect checks.',
                    'Run this after you\'ve got fresh crawl + audit data — an empty/stale dataset just produces a false "fail" for missing data, not a real signal.',
                    'Use the pass/warn/fail verdict as an actual release gate, not just a report.',
                ],
                'release-qa.show' => [
                    'Every issue that contributed to this run\'s score, with severity — fix high-severity items before deploying.',
                ],
                'checklist.index' => [
                    'Auto-graded from your latest crawl, latest audit, GSC data, and website settings — each item explains exactly why it passed, warned, or failed.',
                    '"Generate tasks" converts every warn/fail item into a due-dated task (3 days for fails, 7 for warnings) without creating duplicates.',
                    'Re-run a crawl or audit and revisit this page — scores update automatically, nothing to regenerate manually.',
                ],
                'audits.index' => [
                    '"Run audit" fetches the URL live by default — or paste raw HTML instead to audit content behind auth or before it\'s published.',
                    'Checks title/meta description length, H1 count, canonical tag, missing alt text, word count, and internal/external link counts.',
                    'An unreachable URL returns a clear error instead of crashing — double-check the URL if you see one.',
                ],
                'audits.show' => [
                    'Full breakdown for one audited URL — use the issues list as a literal fix checklist for that page.',
                ],
                'guide.index' => [
                    'A plain-language front door to every page in this app — what to set up first, how each page gets its data, and what runs automatically.',
                    'Click any page card to jump straight there.',
                ],
            ];
            $helpItems = $helpByRoute[$routeName] ?? ['Use this page to manage SEO workflows.', 'Keep data updated to improve alerts, reports, and checklist quality.', 'See FUNCTIONALITY.md in the project root for the full app guide.'];
        @endphp

        @if (session('status'))
            <div class="mb-6 rounded-md border border-emerald-400/40 bg-emerald-900/30 px-4 py-3 text-sm text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-md border border-red-400/40 bg-red-900/30 px-4 py-3 text-sm text-red-200">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
            <div>
                {{ $slot }}
            </div>
            <aside class="h-fit max-w-full overflow-hidden rounded-xl border border-slate-800 bg-slate-900/70 p-4 xl:sticky xl:top-6">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-300">How To Use This Page</h2>
                <ul class="mt-3 list-disc space-y-2 pl-5 text-sm text-slate-400 [overflow-wrap:anywhere] [word-break:break-word]">
                    @foreach ($helpItems as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </aside>
        </div>
    </main>
</div>
</body>
</html>
