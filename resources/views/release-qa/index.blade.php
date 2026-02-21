<x-layouts.app :title="'Release QA - SEO Toolkit'">
    <h1 class="text-2xl font-semibold">Technical QA for Releases</h1>
    <p class="mt-1 text-sm text-slate-400">Gate deployments with SEO checks from crawl, audits, alerts, and redirects.</p>

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
            <h2 class="text-lg font-semibold">Run QA</h2>
            <form action="{{ route('release-qa.run') }}" method="post" class="mt-4 grid gap-3 md:grid-cols-3">
                @csrf
                <input type="hidden" name="website_id" value="{{ $selectedWebsite->id }}">
                <select name="environment" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                    <option value="staging">Staging</option>
                    <option value="preview">Preview</option>
                    <option value="production">Production</option>
                </select>
                <input name="release_tag" placeholder="release-2026-02-21" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                <button class="rounded-md bg-orange-400 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-orange-300">Run release QA</button>
            </form>
        </section>

        <section class="mt-6 overflow-x-auto rounded-xl border border-slate-800 bg-slate-900/70">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="px-3 py-3">Checked</th>
                        <th class="px-3 py-3">Env</th>
                        <th class="px-3 py-3">Release</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3">Score</th>
                        <th class="px-3 py-3">Issues</th>
                        <th class="px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($runs as $run)
                        <tr class="border-b border-slate-900/70">
                            <td class="px-3 py-3">{{ $run->checked_at->format('Y-m-d H:i') }}</td>
                            <td class="px-3 py-3">{{ $run->environment }}</td>
                            <td class="px-3 py-3">{{ $run->release_tag ?? '-' }}</td>
                            <td class="px-3 py-3 uppercase">{{ $run->status }}</td>
                            <td class="px-3 py-3">{{ $run->score }}</td>
                            <td class="px-3 py-3">{{ $run->summary['total_issues'] ?? 0 }}</td>
                            <td class="px-3 py-3"><a href="{{ route('release-qa.show', $run) }}" class="text-sky-300 hover:text-sky-200">Open</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-3 py-5 text-slate-500">No QA runs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        @if ($runs instanceof \Illuminate\Contracts\Pagination\Paginator)
            <div class="mt-6">{{ $runs->links() }}</div>
        @endif
    @endif
</x-layouts.app>
