<x-layouts.app :title="'Domain Overview - SEO Toolkit'">
    <h1 class="text-2xl font-semibold">Domain Overview</h1>
    <p class="mt-1 text-sm text-slate-400">SEMrush-style domain trends: traffic, visibility, keywords, and links.</p>

    <form method="get" class="mt-5 rounded-xl border border-slate-800 bg-slate-900/70 p-4">
        <label class="text-sm text-slate-300">Website</label>
        <div class="mt-2 flex gap-2">
            <select name="website_id" class="w-full rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" onchange="this.form.submit()">
                @foreach ($websites as $site)
                    <option value="{{ $site->id }}" @selected($selectedWebsite && $selectedWebsite->id === $site->id)>{{ $site->name }}</option>
                @endforeach
            </select>
            <button class="rounded-md border border-slate-700 px-4 py-2 text-sm">Load</button>
        </div>
    </form>

    @if ($selectedWebsite)
        <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @php $latest = $snapshots->last(); @endphp
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-400">Estimated Traffic</p>
                <p class="mt-2 text-3xl font-semibold">{{ number_format($latest?->estimated_traffic ?? 0) }}</p>
            </article>
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-400">Organic Keywords</p>
                <p class="mt-2 text-3xl font-semibold">{{ number_format($latest?->organic_keywords ?? 0) }}</p>
            </article>
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
                <p class="text-xs uppercase tracking-wide text-slate-400">Visibility Index</p>
                <p class="mt-2 text-3xl font-semibold">{{ $latest?->visibility_index ?? 0 }}</p>
            </article>
        </section>

        <section class="mt-6 grid gap-6 lg:grid-cols-2">
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
                <h2 class="text-lg font-semibold">Log Snapshot</h2>
                <form action="{{ route('domain-overview.store', $selectedWebsite) }}" method="post" class="mt-4 grid gap-3">
                    @csrf
                    <input type="datetime-local" name="snapshot_at" value="{{ now()->format('Y-m-d\\TH:i') }}" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" required>
                    <div class="grid grid-cols-2 gap-3">
                        <input name="estimated_traffic" type="number" min="0" placeholder="Estimated traffic" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                        <input name="organic_keywords" type="number" min="0" placeholder="Organic keywords" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                        <input name="referring_domains" type="number" min="0" placeholder="Referring domains" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                        <input name="backlinks_count" type="number" min="0" placeholder="Backlinks" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                        <input name="visibility_index" type="number" min="0" max="100" placeholder="Visibility index" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                        <input name="avg_position" type="number" min="1" max="1000" placeholder="Average position" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                    </div>
                    <button class="rounded-md bg-sky-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-sky-400">Save snapshot</button>
                </form>
            </article>

            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
                <h2 class="text-lg font-semibold">Trend Table</h2>
                <div class="mt-4 max-h-[24rem] overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-800 text-left text-slate-400">
                                <th class="px-2 py-2">Date</th>
                                <th class="px-2 py-2">Traffic</th>
                                <th class="px-2 py-2">Keywords</th>
                                <th class="px-2 py-2">Visibility</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($snapshots->reverse() as $snapshot)
                                <tr class="border-b border-slate-900/70">
                                    <td class="px-2 py-2">{{ $snapshot->snapshot_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-2 py-2">{{ number_format($snapshot->estimated_traffic ?? 0) }}</td>
                                    <td class="px-2 py-2">{{ number_format($snapshot->organic_keywords ?? 0) }}</td>
                                    <td class="px-2 py-2">{{ $snapshot->visibility_index ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-2 py-4 text-slate-500">No data yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </section>
    @endif
</x-layouts.app>
