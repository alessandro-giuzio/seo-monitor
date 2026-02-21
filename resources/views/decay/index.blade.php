<x-layouts.app :title="'Content Decay - SEO Toolkit'">
    <h1 class="text-2xl font-semibold">Content Decay Detector</h1>
    <p class="mt-1 text-sm text-slate-400">Compare last 28 days vs previous 28 days and flag losing pages.</p>

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

    <section class="mt-6 overflow-x-auto rounded-xl border border-slate-800 bg-slate-900/70">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-left text-slate-400">
                    <th class="px-3 py-3">Page</th>
                    <th class="px-3 py-3">Prev 28d Clicks</th>
                    <th class="px-3 py-3">Last 28d Clicks</th>
                    <th class="px-3 py-3">Drop %</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="border-b border-slate-900/70">
                        <td class="px-3 py-3 max-w-[32rem] truncate">{{ $row['url'] }}</td>
                        <td class="px-3 py-3">{{ number_format($row['previous_clicks']) }}</td>
                        <td class="px-3 py-3">{{ number_format($row['last_clicks']) }}</td>
                        <td class="px-3 py-3 text-red-300">{{ $row['drop_percent'] }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-3 py-5 text-slate-500">No decay signals yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</x-layouts.app>
