<x-layouts.app :title="'Technical SEO - SEO Toolkit'">
    <h1 class="text-2xl font-semibold">Technical SEO Monitor</h1>
    <p class="mt-1 text-sm text-slate-400">Sitemap + robots checks, indexation monitor, and crawl snapshots.</p>

    <section class="mt-6 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
        <form method="get" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <select name="website_id" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                @foreach ($websites as $website)
                    <option value="{{ $website->id }}" @selected($selectedWebsite && $selectedWebsite->id === $website->id)>{{ $website->name }}</option>
                @endforeach
            </select>
            <button class="rounded-md border border-slate-700 px-3 py-2 text-sm">Load website</button>
        </form>

        @if ($selectedWebsite)
            <form action="{{ route('technical.run') }}" method="post" class="mt-4 grid gap-3 sm:grid-cols-3">
                @csrf
                <input type="hidden" name="website_id" value="{{ $selectedWebsite->id }}">
                <input type="number" min="5" max="200" name="max_pages" value="30" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" placeholder="Max pages">
                <button class="rounded-md bg-orange-400 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-orange-300">Run crawl now</button>
                @if ($latestRun)
                    <a href="{{ route('technical.runs.show', $latestRun) }}" class="rounded-md border border-slate-700 px-4 py-2 text-center text-sm hover:border-sky-400">Open latest run</a>
                @endif
            </form>
        @endif
    </section>

    @if ($latestRun)
        <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><p class="text-xs uppercase text-slate-400">Started</p><p class="mt-1 text-sm">{{ $latestRun->started_at }}</p></article>
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><p class="text-xs uppercase text-slate-400">Pages Crawled</p><p class="mt-1 text-3xl font-semibold">{{ $latestRun->pages_crawled }}</p></article>
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><p class="text-xs uppercase text-slate-400">Indexable</p><p class="mt-1 text-3xl font-semibold">{{ $latestRun->pages()->where('is_indexable', true)->count() }}</p></article>
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><p class="text-xs uppercase text-slate-400">Orphans</p><p class="mt-1 text-3xl font-semibold">{{ $latestRun->pages()->where('is_orphan', true)->count() }}</p></article>
        </section>

        <section class="mt-6 overflow-x-auto rounded-xl border border-slate-800 bg-slate-900/70">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="px-3 py-3">URL</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3">Indexable</th>
                        <th class="px-3 py-3">In Sitemap</th>
                        <th class="px-3 py-3">Orphan</th>
                        <th class="px-3 py-3">Inlinks</th>
                        <th class="px-3 py-3">Issues</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pages as $page)
                        <tr class="border-b border-slate-900/70">
                            <td class="px-3 py-3 max-w-[32rem] truncate">{{ $page->url }}</td>
                            <td class="px-3 py-3">{{ $page->status_code ?? '-' }}</td>
                            <td class="px-3 py-3">{{ $page->is_indexable ? 'yes' : 'no' }}</td>
                            <td class="px-3 py-3">{{ $page->is_in_sitemap ? 'yes' : 'no' }}</td>
                            <td class="px-3 py-3">{{ $page->is_orphan ? 'yes' : 'no' }}</td>
                            <td class="px-3 py-3">{{ $page->internal_inlinks }}</td>
                            <td class="px-3 py-3">{{ implode('; ', $page->issues ?? []) ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-3 py-5 text-slate-500">No crawl data yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        @if ($pages instanceof \Illuminate\Contracts\Pagination\Paginator)
            <div class="mt-6">{{ $pages->links() }}</div>
        @endif
    @endif
</x-layouts.app>
