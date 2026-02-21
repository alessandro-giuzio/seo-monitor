<x-layouts.app :title="'Backlink Analytics - SEO Toolkit'">
    <h1 class="text-2xl font-semibold">Backlink Analytics + Audit</h1>
    <p class="mt-1 text-sm text-slate-400">Track new links, nofollow ratio, toxicity flags, and link authority.</p>

    <section class="mt-6 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
        <form method="get" class="grid gap-3 md:grid-cols-4">
            <select name="website_id" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                @foreach ($websites as $website)
                    <option value="{{ $website->id }}" @selected($selectedWebsite && $selectedWebsite->id === $website->id)>{{ $website->name }}</option>
                @endforeach
            </select>
            <label class="flex items-center gap-2 rounded-md border border-slate-700 px-3 py-2 text-sm">
                <input type="checkbox" name="toxic_only" value="1" @checked(request('toxic_only'))>
                Toxic only
            </label>
            <label class="flex items-center gap-2 rounded-md border border-slate-700 px-3 py-2 text-sm">
                <input type="checkbox" name="nofollow_only" value="1" @checked(request('nofollow_only'))>
                Nofollow only
            </label>
            <button class="rounded-md border border-slate-700 px-3 py-2 text-sm hover:border-sky-400">Apply</button>
        </form>
    </section>

    @if ($selectedWebsite)
        <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><p class="text-xs uppercase text-slate-400">Total</p><p class="mt-2 text-3xl font-semibold">{{ number_format($stats['total']) }}</p></article>
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><p class="text-xs uppercase text-slate-400">Toxic</p><p class="mt-2 text-3xl font-semibold text-red-300">{{ number_format($stats['toxic']) }}</p></article>
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><p class="text-xs uppercase text-slate-400">Nofollow</p><p class="mt-2 text-3xl font-semibold">{{ number_format($stats['nofollow']) }}</p></article>
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><p class="text-xs uppercase text-slate-400">Avg Authority</p><p class="mt-2 text-3xl font-semibold">{{ $stats['avg_authority'] }}</p></article>
        </section>

        <section class="mt-6 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h2 class="text-lg font-semibold">Add Backlink</h2>
            <form action="{{ route('backlinks.store') }}" method="post" class="mt-4 grid gap-3">
                @csrf
                <input type="hidden" name="website_id" value="{{ $selectedWebsite->id }}">
                <input name="source_url" placeholder="https://ref-domain.com/post" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" required>
                <input name="target_url" value="{{ $selectedWebsite->base_url }}" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" required>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
                    <input name="anchor_text" placeholder="Anchor" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm md:col-span-2">
                    <input name="source_authority" type="number" min="0" max="100" placeholder="Authority" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                    <select name="is_nofollow" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm"><option value="0">Follow</option><option value="1">Nofollow</option></select>
                    <select name="is_toxic" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm"><option value="0">Healthy</option><option value="1">Toxic</option></select>
                    <input type="datetime-local" name="found_at" value="{{ now()->format('Y-m-d\\TH:i') }}" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                    <input type="datetime-local" name="last_seen_at" value="{{ now()->format('Y-m-d\\TH:i') }}" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                </div>
                <button class="rounded-md bg-sky-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-sky-400">Save backlink</button>
            </form>
        </section>

        <section class="mt-6 overflow-x-auto rounded-xl border border-slate-800 bg-slate-900/70">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="px-3 py-3">Source</th>
                        <th class="px-3 py-3">Target</th>
                        <th class="px-3 py-3">Anchor</th>
                        <th class="px-3 py-3">Authority</th>
                        <th class="px-3 py-3">Flags</th>
                        <th class="px-3 py-3">Seen</th>
                        <th class="px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($backlinks as $backlink)
                        <tr class="border-b border-slate-900/70">
                            <td class="px-3 py-3"><a href="{{ $backlink->source_url }}" class="text-sky-300 hover:text-sky-200" target="_blank">{{ $backlink->source_url }}</a></td>
                            <td class="px-3 py-3">{{ $backlink->target_url }}</td>
                            <td class="px-3 py-3">{{ $backlink->anchor_text ?: '-' }}</td>
                            <td class="px-3 py-3">{{ $backlink->source_authority ?? '-' }}</td>
                            <td class="px-3 py-3">
                                @if ($backlink->is_toxic)<span class="text-red-300">Toxic</span>@else<span class="text-emerald-300">Healthy</span>@endif
                                · {{ $backlink->is_nofollow ? 'Nofollow' : 'Follow' }}
                            </td>
                            <td class="px-3 py-3">{{ optional($backlink->last_seen_at)->format('Y-m-d') ?: '-' }}</td>
                            <td class="px-3 py-3">
                                <form action="{{ route('backlinks.destroy', $backlink) }}" method="post" onsubmit="return confirm('Delete backlink?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded border border-red-500/50 px-2 py-1 text-xs text-red-300">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-3 py-5 text-slate-500">No backlinks yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        @if ($backlinks instanceof \Illuminate\Contracts\Pagination\Paginator)
            <div class="mt-6">{{ $backlinks->links() }}</div>
        @endif
    @endif
</x-layouts.app>
