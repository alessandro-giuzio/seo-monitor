<x-layouts.app :title="'Release QA Run #' . $run->id">
    <h1 class="text-2xl font-semibold">Release QA Run #{{ $run->id }}</h1>
    <p class="mt-1 text-sm text-slate-400">{{ $run->website->name }} · {{ $run->environment }} · {{ $run->checked_at }}</p>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><p class="text-xs uppercase text-slate-400">Status</p><p class="mt-2 text-3xl font-semibold uppercase">{{ $run->status }}</p></article>
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><p class="text-xs uppercase text-slate-400">Score</p><p class="mt-2 text-3xl font-semibold">{{ $run->score }}</p></article>
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><p class="text-xs uppercase text-slate-400">High Issues</p><p class="mt-2 text-3xl font-semibold">{{ $run->summary['high_issues'] ?? 0 }}</p></article>
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4"><p class="text-xs uppercase text-slate-400">Medium Issues</p><p class="mt-2 text-3xl font-semibold">{{ $run->summary['medium_issues'] ?? 0 }}</p></article>
    </section>

    <section class="mt-6 overflow-x-auto rounded-xl border border-slate-800 bg-slate-900/70">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-left text-slate-400">
                    <th class="px-3 py-3">Severity</th>
                    <th class="px-3 py-3">Category</th>
                    <th class="px-3 py-3">Title</th>
                    <th class="px-3 py-3">Details</th>
                    <th class="px-3 py-3">URL</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($run->issues as $issue)
                    <tr class="border-b border-slate-900/70 align-top">
                        <td class="px-3 py-3 uppercase">{{ $issue->severity }}</td>
                        <td class="px-3 py-3">{{ $issue->category }}</td>
                        <td class="px-3 py-3">{{ $issue->title }}</td>
                        <td class="px-3 py-3">{{ $issue->details }}</td>
                        <td class="px-3 py-3 max-w-[20rem] truncate">{{ $issue->url ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-5 text-emerald-300">No issues found. Release looks good.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</x-layouts.app>
