<x-layouts.app :title="'Internal Link Opportunities - SEO Toolkit'">
    <h1 class="text-2xl font-semibold">Internal Link Opportunities</h1>
    <p class="mt-1 text-sm text-slate-400">Generated from latest technical crawl data.</p>

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
                    <th class="px-3 py-3">Score</th>
                    <th class="px-3 py-3">Source URL</th>
                    <th class="px-3 py-3">Target URL</th>
                    <th class="px-3 py-3">Reason</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($opportunities as $opportunity)
                    <tr class="border-b border-slate-900/70">
                        <td class="px-3 py-3">{{ $opportunity->priority_score }}</td>
                        <td class="px-3 py-3 max-w-[20rem] truncate">{{ $opportunity->source_url }}</td>
                        <td class="px-3 py-3 max-w-[20rem] truncate">{{ $opportunity->target_url }}</td>
                        <td class="px-3 py-3">{{ $opportunity->reason }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-3 py-5 text-slate-500">No opportunities generated yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    @if ($opportunities instanceof \Illuminate\Contracts\Pagination\Paginator)
        <div class="mt-6">{{ $opportunities->links() }}</div>
    @endif
</x-layouts.app>
