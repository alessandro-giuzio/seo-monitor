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
    <header class="border-b border-slate-800/70 bg-slate-950/80 backdrop-blur">
        <nav class="mx-auto flex max-w-7xl items-start justify-between gap-8 px-6 py-4 lg:items-center">
            <div class="shrink-0">
                <a href="{{ route('dashboard') }}" class="text-lg font-semibold tracking-tight">SEO Toolkit</a>
                <p class="text-xs text-slate-400">Research + Monitoring</p>
            </div>
            <div class="flex flex-1 flex-wrap items-center justify-start gap-2 pt-1 text-sm lg:justify-end lg:pt-0">
                <a href="{{ route('dashboard') }}" class="rounded-md border border-slate-700 px-3 py-1.5 hover:border-sky-400 hover:text-sky-300">Dashboard</a>
                <a href="{{ route('websites.index') }}" class="rounded-md border border-slate-700 px-3 py-1.5 hover:border-sky-400 hover:text-sky-300">Websites</a>
                <a href="{{ route('gsc.index') }}" class="rounded-md border border-slate-700 px-3 py-1.5 hover:border-sky-400 hover:text-sky-300">GSC</a>
                <a href="{{ route('domain-overview.index') }}" class="rounded-md border border-slate-700 px-3 py-1.5 hover:border-sky-400 hover:text-sky-300">Domain</a>
                <a href="{{ route('keyword-research.index') }}" class="rounded-md border border-slate-700 px-3 py-1.5 hover:border-sky-400 hover:text-sky-300">Keywords</a>
                <a href="{{ route('competitors.index') }}" class="rounded-md border border-slate-700 px-3 py-1.5 hover:border-sky-400 hover:text-sky-300">Gap</a>
                <a href="{{ route('backlinks.index') }}" class="rounded-md border border-slate-700 px-3 py-1.5 hover:border-sky-400 hover:text-sky-300">Backlinks</a>
                <a href="{{ route('technical.index') }}" class="rounded-md border border-slate-700 px-3 py-1.5 hover:border-sky-400 hover:text-sky-300">Technical</a>
                <a href="{{ route('decay.index') }}" class="rounded-md border border-slate-700 px-3 py-1.5 hover:border-sky-400 hover:text-sky-300">Decay</a>
                <a href="{{ route('links.index') }}" class="rounded-md border border-slate-700 px-3 py-1.5 hover:border-sky-400 hover:text-sky-300">Link Ops</a>
                <a href="{{ route('alerts.index') }}" class="rounded-md border border-slate-700 px-3 py-1.5 hover:border-sky-400 hover:text-sky-300">Alerts</a>
                <a href="{{ route('reports.index') }}" class="rounded-md border border-slate-700 px-3 py-1.5 hover:border-sky-400 hover:text-sky-300">Reports</a>
                <a href="{{ route('change-log.index') }}" class="rounded-md border border-slate-700 px-3 py-1.5 hover:border-sky-400 hover:text-sky-300">Change Log</a>
                <a href="{{ route('redirects.index') }}" class="rounded-md border border-slate-700 px-3 py-1.5 hover:border-sky-400 hover:text-sky-300">Redirects</a>
                <a href="{{ route('release-qa.index') }}" class="rounded-md border border-slate-700 px-3 py-1.5 hover:border-sky-400 hover:text-sky-300">Release QA</a>
                <a href="{{ route('checklist.index') }}" class="rounded-md border border-slate-700 px-3 py-1.5 hover:border-sky-400 hover:text-sky-300">Checklist</a>
                <a href="{{ route('audits.index') }}" class="rounded-md border border-slate-700 px-3 py-1.5 hover:border-sky-400 hover:text-sky-300">Audits</a>
            </div>
        </nav>
    </header>

    <main class="mx-auto max-w-7xl px-6 py-8">
        @php
            $routeName = request()->route()?->getName();
            $helpByRoute = [
                'dashboard' => ['Use this page to add websites, run audits, and log keyword/uptime updates quickly.', 'Start by adding a website, then add tracked keywords.', 'Use this daily as your operations inbox.'],
                'websites.index' => ['Review all websites you track.', 'Open each website for settings and recent monitoring data.'],
                'websites.show' => ['Update website settings like GSC property, alert email, and crawl frequency.', 'Use this as the profile page for one website.'],
                'gsc.index' => ['Paste GSC export rows in format: date,query,page_url,clicks,impressions,ctr,avg_position.', 'Import data regularly to power decay and alert modules.'],
                'domain-overview.index' => ['Log periodic domain metrics snapshots.', 'Use trends to monitor visibility and traffic direction.'],
                'keyword-research.index' => ['Store and filter keyword ideas.', 'Promote ideas into tracked keywords with Track button.'],
                'competitors.index' => ['Add competitors and their keyword snapshots.', 'Use Keyword Gap table to prioritize opportunities.'],
                'backlinks.index' => ['Track backlink quality and toxicity.', 'Filter toxic/nofollow links and audit regularly.'],
                'technical.index' => ['Run a crawl to check robots, sitemap, indexation, orphan pages, and depth.', 'Crawl data feeds checklist, alerts, and link opportunities.'],
                'technical.runs.show' => ['Review one crawl run summary and generated internal link opportunities.', 'Use this after each crawl to assign fixes.'],
                'decay.index' => ['Find pages losing clicks versus prior period.', 'Refresh or relink pages with biggest drops.'],
                'links.index' => ['Review suggested internal links.', 'Implement high-score links first.'],
                'alerts.index' => ['Run alert evaluation and resolve handled alerts.', 'Open alerts show current risks.'],
                'reports.index' => ['Generate weekly snapshot and export CSV for sharing.', 'Use this for client/team reporting.'],
                'change-log.index' => ['Track what changed (content, metadata, redirects, technical) and when.', 'Use this to correlate SEO changes with ranking/traffic shifts.'],
                'redirects.index' => ['Maintain redirect mappings and validate response/location behavior.', 'Use Check action before releases to avoid broken redirect rollouts.'],
                'release-qa.index' => ['Run pre-release SEO QA against crawl/audit/alerts/redirect health.', 'Use pass/warn/fail score as release gate before go-live.'],
                'release-qa.show' => ['Inspect one release QA run and all detected issues.', 'Fix high severity items before deploying.'],
                'checklist.index' => ['Follow the 4-block SEO checklist and resolve warn/fail items.', 'Use Generate tasks to convert issues into actionable work.'],
                'audits.index' => ['Browse all on-page audits.', 'Open specific audits for issue-level detail.'],
                'audits.show' => ['Review metadata and technical findings for one URL.', 'Use issues list as optimization checklist for the page.'],
            ];
            $helpItems = $helpByRoute[$routeName] ?? ['Use this page to manage SEO workflows.', 'Keep data updated to improve alerts, reports, and checklist quality.'];
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
