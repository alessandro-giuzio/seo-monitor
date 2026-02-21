<x-layouts.app :title="'Keyword Research - SEO Toolkit'">
    <h1 class="text-2xl font-semibold">Keyword Research</h1>
    <p class="mt-1 text-sm text-slate-400">Keyword Magic style workspace: collect, filter, and push ideas to tracking.</p>

    <section class="mt-6 grid gap-4 sm:grid-cols-2">
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
            <p class="text-xs uppercase tracking-wide text-slate-400">Total ideas</p>
            <p class="mt-2 text-3xl font-semibold">{{ number_format($stats['total_ideas']) }}</p>
        </article>
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
            <p class="text-xs uppercase tracking-wide text-slate-400">Low KD + High Volume</p>
            <p class="mt-2 text-3xl font-semibold">{{ number_format($stats['low_kd_high_volume']) }}</p>
        </article>
    </section>

    <section class="mt-6 grid gap-6 lg:grid-cols-2">
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h2 class="text-lg font-semibold">Add Keyword Idea</h2>
            <form action="{{ route('keyword-research.store') }}" method="post" class="mt-4 grid gap-3">
                @csrf
                <select name="website_id" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                    <option value="">Unlinked</option>
                    @foreach ($websites as $website)
                        <option value="{{ $website->id }}">{{ $website->name }}</option>
                    @endforeach
                </select>
                <div class="grid grid-cols-2 gap-3">
                    <input name="seed_keyword" placeholder="Seed keyword" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                    <input name="keyword" placeholder="Keyword idea" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" required>
                    <input name="search_volume" type="number" min="0" placeholder="Volume" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                    <input name="keyword_difficulty" type="number" min="0" max="100" placeholder="KD" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                    <input name="cpc" type="number" step="0.01" min="0" placeholder="CPC" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                    <input name="country" maxlength="2" placeholder="Country" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                </div>
                <select name="intent" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                    <option value="">Intent</option>
                    <option value="informational">Informational</option>
                    <option value="navigational">Navigational</option>
                    <option value="commercial">Commercial</option>
                    <option value="transactional">Transactional</option>
                    <option value="mixed">Mixed</option>
                </select>
                <button class="rounded-md bg-sky-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-sky-400">Save idea</button>
            </form>
        </article>

        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h2 class="text-lg font-semibold">Bulk Import</h2>
            <p class="mt-1 text-xs text-slate-500">Format per line: keyword,volume,kd,cpc,intent</p>
            <form action="{{ route('keyword-research.bulk-store') }}" method="post" class="mt-4 grid gap-3">
                @csrf
                <select name="website_id" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                    <option value="">Unlinked</option>
                    @foreach ($websites as $website)
                        <option value="{{ $website->id }}">{{ $website->name }}</option>
                    @endforeach
                </select>
                <div class="grid grid-cols-2 gap-3">
                    <input name="seed_keyword" placeholder="Seed keyword" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                    <input name="country" maxlength="2" placeholder="Country" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                </div>
                <textarea name="rows" rows="7" placeholder="seo dashboard,2400,41,1.90,commercial" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" required></textarea>
                <button class="rounded-md bg-orange-400 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-orange-300">Import rows</button>
            </form>
        </article>
    </section>

    <section class="mt-6 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
        <h2 class="text-lg font-semibold">Filters</h2>
        <form method="get" class="mt-3 grid gap-3 md:grid-cols-3 lg:grid-cols-6">
            <select name="website_id" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                <option value="">All websites</option>
                @foreach ($websites as $website)
                    <option value="{{ $website->id }}" @selected(($filters['website_id'] ?? null) == $website->id)>{{ $website->name }}</option>
                @endforeach
            </select>
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Keyword" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
            <input name="country" value="{{ $filters['country'] ?? '' }}" maxlength="2" placeholder="Country" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
            <select name="intent" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                <option value="">Any intent</option>
                @foreach (['informational','navigational','commercial','transactional','mixed'] as $intent)
                    <option value="{{ $intent }}" @selected(($filters['intent'] ?? null) === $intent)>{{ ucfirst($intent) }}</option>
                @endforeach
            </select>
            <input name="min_volume" value="{{ $filters['min_volume'] ?? '' }}" type="number" min="0" placeholder="Min volume" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
            <input name="max_kd" value="{{ $filters['max_kd'] ?? '' }}" type="number" min="0" max="100" placeholder="Max KD" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
            <button class="rounded-md border border-slate-700 px-3 py-2 text-sm hover:border-sky-400">Apply</button>
        </form>
    </section>

    <section class="mt-6 overflow-x-auto rounded-xl border border-slate-800 bg-slate-900/70">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-left text-slate-400">
                    <th class="px-3 py-3">Keyword</th>
                    <th class="px-3 py-3">Website</th>
                    <th class="px-3 py-3">Volume</th>
                    <th class="px-3 py-3">KD</th>
                    <th class="px-3 py-3">CPC</th>
                    <th class="px-3 py-3">Intent</th>
                    <th class="px-3 py-3">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ideas as $idea)
                    <tr class="border-b border-slate-900/70">
                        <td class="px-3 py-3">{{ $idea->keyword }}</td>
                        <td class="px-3 py-3">{{ $idea->website?->name ?? 'Unlinked' }}</td>
                        <td class="px-3 py-3">{{ number_format($idea->search_volume ?? 0) }}</td>
                        <td class="px-3 py-3">{{ $idea->keyword_difficulty ?? '-' }}</td>
                        <td class="px-3 py-3">{{ $idea->cpc !== null ? '$'.number_format((float) $idea->cpc, 2) : '-' }}</td>
                        <td class="px-3 py-3 capitalize">{{ $idea->intent ?? '-' }}</td>
                        <td class="px-3 py-3">
                            <form action="{{ route('keyword-research.track', $idea) }}" method="post">
                                @csrf
                                <button class="rounded-md border border-slate-700 px-2 py-1 text-xs hover:border-sky-400">Track</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-3 py-5 text-slate-500">No ideas found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <div class="mt-6">{{ $ideas->links() }}</div>
</x-layouts.app>
