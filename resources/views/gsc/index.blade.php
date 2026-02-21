<x-layouts.app :title="'GSC Sync - SEO Toolkit'">
    <h1 class="text-2xl font-semibold">Google Search Console Sync</h1>
    <p class="mt-1 text-sm text-slate-400">Import query/page performance rows from GSC export CSV.</p>

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

    @if ($selectedWebsite)
        <section class="mt-6 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h2 class="text-lg font-semibold">Import Rows</h2>
            <p class="mt-1 text-xs text-slate-500">Format: date,query,page_url,clicks,impressions,ctr,avg_position</p>
            <form action="{{ route('gsc.import') }}" method="post" class="mt-4 grid gap-3">
                @csrf
                <input type="hidden" name="website_id" value="{{ $selectedWebsite->id }}">
                <textarea name="rows" rows="8" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" placeholder="2026-02-20,seo toolkit,https://example.com/seo-toolkit,53,1200,0.0442,12.8" required></textarea>
                <button class="rounded-md bg-sky-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-sky-400">Import GSC data</button>
            </form>
        </section>

        <section class="mt-6 overflow-x-auto rounded-xl border border-slate-800 bg-slate-900/70">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="px-3 py-3">Date</th>
                        <th class="px-3 py-3">Query</th>
                        <th class="px-3 py-3">Page</th>
                        <th class="px-3 py-3">Clicks</th>
                        <th class="px-3 py-3">Impressions</th>
                        <th class="px-3 py-3">CTR</th>
                        <th class="px-3 py-3">Pos</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($metrics as $metric)
                        <tr class="border-b border-slate-900/70">
                            <td class="px-3 py-3">{{ $metric->metric_date->toDateString() }}</td>
                            <td class="px-3 py-3">{{ $metric->query ?? '-' }}</td>
                            <td class="px-3 py-3 truncate max-w-[24rem]">{{ $metric->page_url ?? '-' }}</td>
                            <td class="px-3 py-3">{{ number_format($metric->clicks) }}</td>
                            <td class="px-3 py-3">{{ number_format($metric->impressions) }}</td>
                            <td class="px-3 py-3">{{ $metric->ctr !== null ? round($metric->ctr * 100, 2).'%' : '-' }}</td>
                            <td class="px-3 py-3">{{ $metric->avg_position ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-3 py-5 text-slate-500">No GSC rows imported yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        @if ($metrics instanceof \Illuminate\Contracts\Pagination\Paginator)
            <div class="mt-6">{{ $metrics->links() }}</div>
        @endif
    @endif
</x-layouts.app>
