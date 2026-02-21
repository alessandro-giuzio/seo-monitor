<x-layouts.app :title="'SEO Reports - SEO Toolkit'">
    <h1 class="text-2xl font-semibold">Weekly SEO Report</h1>
    <p class="mt-1 text-sm text-slate-400">Simple snapshot with exportable CSV.</p>

    <section class="mt-6 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
        <form method="get" class="flex gap-2">
            <select name="website_id" class="w-full rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" onchange="this.form.submit()">
                @foreach ($websites as $website)
                    <option value="{{ $website->id }}" @selected($selectedWebsite && $selectedWebsite->id === $website->id)>{{ $website->name }}</option>
                @endforeach
            </select>
            <button class="rounded-md border border-slate-700 px-3 py-2 text-sm">Load</button>
        </form>
    </section>

    @if ($selectedWebsite && $report)
        <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><p class="text-xs uppercase text-slate-400">Tracked Keywords</p><p class="mt-2 text-3xl font-semibold">{{ $report['tracked_keywords'] }}</p></article>
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><p class="text-xs uppercase text-slate-400">Top 10 Keywords</p><p class="mt-2 text-3xl font-semibold">{{ $report['top_10_keywords'] }}</p></article>
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><p class="text-xs uppercase text-slate-400">Open Alerts</p><p class="mt-2 text-3xl font-semibold">{{ $report['open_alerts'] }}</p></article>
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><p class="text-xs uppercase text-slate-400">Uptime (30d)</p><p class="mt-2 text-3xl font-semibold">{{ $report['uptime_rate_30d'] !== null ? $report['uptime_rate_30d'].'%' : 'n/a' }}</p></article>
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><p class="text-xs uppercase text-slate-400">Indexable (Latest Crawl)</p><p class="mt-2 text-3xl font-semibold">{{ $report['latest_crawl_indexable'] }}</p></article>
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><p class="text-xs uppercase text-slate-400">Orphans (Latest Crawl)</p><p class="mt-2 text-3xl font-semibold">{{ $report['latest_crawl_orphans'] }}</p></article>
        </section>

        <section class="mt-6">
            <a href="{{ route('reports.csv', ['website_id' => $selectedWebsite->id]) }}" class="rounded-md bg-sky-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-sky-400">Download CSV report</a>
        </section>

        <section class="mt-6 overflow-x-auto rounded-xl border border-slate-800 bg-slate-900/70">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="px-3 py-3">Decay URL</th>
                        <th class="px-3 py-3">Prev 28d</th>
                        <th class="px-3 py-3">Last 28d</th>
                        <th class="px-3 py-3">Drop %</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($report['decay_rows'] as $row)
                        <tr class="border-b border-slate-900/70">
                            <td class="px-3 py-3 max-w-[36rem] truncate">{{ $row['url'] }}</td>
                            <td class="px-3 py-3">{{ $row['previous_clicks'] }}</td>
                            <td class="px-3 py-3">{{ $row['last_clicks'] }}</td>
                            <td class="px-3 py-3">{{ $row['drop_percent'] }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-5 text-slate-500">No decay rows in current window.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    @endif
</x-layouts.app>
