<x-layouts.app :title="'Competitors & Keyword Gap - SEO Toolkit'">
    <h1 class="text-2xl font-semibold">Competitors + Keyword Gap</h1>
    <p class="mt-1 text-sm text-slate-400">Track competitor rankings and expose opportunities where they outrank you.</p>

    <section class="mt-6 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
        <form method="get" class="flex gap-2">
            <select name="website_id" class="w-full rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" onchange="this.form.submit()">
                @foreach ($websites as $site)
                    <option value="{{ $site->id }}" @selected($selectedWebsite && $selectedWebsite->id === $site->id)>{{ $site->name }}</option>
                @endforeach
            </select>
            <button class="rounded-md border border-slate-700 px-4 py-2 text-sm">Load</button>
        </form>
    </section>

    @if ($selectedWebsite)
        <section class="mt-6 grid gap-6 lg:grid-cols-2">
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
                <h2 class="text-lg font-semibold">Add Competitor</h2>
                <form action="{{ route('competitors.store', $selectedWebsite) }}" method="post" class="mt-4 grid gap-3">
                    @csrf
                    <input name="name" placeholder="Competitor name" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" required>
                    <input name="domain" placeholder="competitor.com" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" required>
                    <input name="notes" placeholder="Notes" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                    <button class="rounded-md bg-sky-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-sky-400">Add competitor</button>
                </form>
            </article>

            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
                <h2 class="text-lg font-semibold">Competitors</h2>
                <div class="mt-4 space-y-2 text-sm">
                    @forelse ($selectedWebsite->competitors as $competitor)
                        <div class="rounded-md border border-slate-800 p-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-medium">{{ $competitor->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $competitor->domain }}</p>
                                </div>
                                <form action="{{ route('competitors.destroy', $competitor) }}" method="post" onsubmit="return confirm('Delete competitor?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded border border-red-500/50 px-2 py-1 text-xs text-red-300">Delete</button>
                                </form>
                            </div>
                            <form action="{{ route('competitors.snapshots.store', $competitor) }}" method="post" class="mt-3 grid gap-2 sm:grid-cols-2">
                                @csrf
                                <input name="keyword" placeholder="keyword" class="rounded-md border border-slate-700 bg-slate-950 px-2 py-1 text-xs" required>
                                <input type="datetime-local" name="checked_at" value="{{ now()->format('Y-m-d\\TH:i') }}" class="rounded-md border border-slate-700 bg-slate-950 px-2 py-1 text-xs" required>
                                <input name="position" type="number" min="1" max="1000" placeholder="Position" class="rounded-md border border-slate-700 bg-slate-950 px-2 py-1 text-xs">
                                <input name="search_volume" type="number" min="0" placeholder="Volume" class="rounded-md border border-slate-700 bg-slate-950 px-2 py-1 text-xs">
                                <button class="rounded-md bg-orange-400 px-2 py-1 text-xs font-medium text-slate-950 hover:bg-orange-300 sm:col-span-2">Add ranking snapshot</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-slate-500">No competitors yet.</p>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="mt-6 overflow-x-auto rounded-xl border border-slate-800 bg-slate-900/70">
            <div class="border-b border-slate-800 px-4 py-3">
                <h2 class="text-lg font-semibold">Keyword Gap Opportunities</h2>
                <p class="text-xs text-slate-500">Competitor ranks top 30 and you do not rank or rank lower.</p>
            </div>
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="px-3 py-3">Keyword</th>
                        <th class="px-3 py-3">Competitor</th>
                        <th class="px-3 py-3">Competitor Pos</th>
                        <th class="px-3 py-3">Your Pos</th>
                        <th class="px-3 py-3">Volume</th>
                        <th class="px-3 py-3">Gap</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($gapRows as $row)
                        <tr class="border-b border-slate-900/70">
                            <td class="px-3 py-3">{{ $row['keyword'] }}</td>
                            <td class="px-3 py-3">{{ $row['competitor'] }}</td>
                            <td class="px-3 py-3">{{ $row['competitor_position'] }}</td>
                            <td class="px-3 py-3">{{ $row['your_position'] ?? '-' }}</td>
                            <td class="px-3 py-3">{{ number_format($row['search_volume'] ?? 0) }}</td>
                            <td class="px-3 py-3">{{ $row['gap'] ?? 'n/a' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-5 text-slate-500">No gap opportunities yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    @endif
</x-layouts.app>
