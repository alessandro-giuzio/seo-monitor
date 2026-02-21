<x-layouts.app :title="'Audit - ' . $audit->url">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">Audit Report</h1>
            <p class="text-sm text-slate-400">{{ $audit->url }}</p>
            <p class="text-xs text-slate-500">{{ $audit->audited_at->format('Y-m-d H:i') }} · {{ $audit->website?->name ?? 'Unlinked website' }}</p>
        </div>
        <a href="{{ route('audits.index') }}" class="rounded-md border border-slate-700 px-3 py-1.5 text-sm hover:border-sky-400 hover:text-sky-300">All audits</a>
    </div>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
        <article class="rounded-lg border border-slate-800 bg-slate-900/70 p-3 lg:col-span-2">
            <p class="text-xs uppercase tracking-wide text-slate-400">Status</p>
            <p class="mt-1 text-2xl font-semibold capitalize">{{ $audit->status }}</p>
        </article>
        <article class="rounded-lg border border-slate-800 bg-slate-900/70 p-3 lg:col-span-2">
            <p class="text-xs uppercase tracking-wide text-slate-400">Score</p>
            <p class="mt-1 text-2xl font-semibold">{{ $audit->score }}/100</p>
        </article>
        <article class="rounded-lg border border-slate-800 bg-slate-900/70 p-3">
            <p class="text-xs uppercase tracking-wide text-slate-400">H1</p>
            <p class="mt-1 text-2xl font-semibold">{{ $audit->h1_count }}</p>
        </article>
        <article class="rounded-lg border border-slate-800 bg-slate-900/70 p-3">
            <p class="text-xs uppercase tracking-wide text-slate-400">Words</p>
            <p class="mt-1 text-2xl font-semibold">{{ $audit->word_count }}</p>
        </article>
    </section>

    <section class="mt-6 grid gap-6 lg:grid-cols-2">
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h2 class="text-lg font-semibold">Metadata</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="text-slate-400">Title</dt>
                    <dd class="mt-1">{{ $audit->title ?? 'Not found' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400">Meta Description</dt>
                    <dd class="mt-1">{{ $audit->meta_description ?? 'Not found' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400">Canonical</dt>
                    <dd class="mt-1">{{ $audit->canonical ?? 'Not found' }}</dd>
                </div>
            </dl>
        </article>

        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h2 class="text-lg font-semibold">Technical Signals</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex items-center justify-between"><dt class="text-slate-400">Images missing alt</dt><dd>{{ $audit->image_without_alt }}</dd></div>
                <div class="flex items-center justify-between"><dt class="text-slate-400">Internal links</dt><dd>{{ $audit->internal_links }}</dd></div>
                <div class="flex items-center justify-between"><dt class="text-slate-400">External links</dt><dd>{{ $audit->external_links }}</dd></div>
            </dl>
        </article>
    </section>

    <section class="mt-6 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
        <h2 class="text-lg font-semibold">Issues</h2>
        <ul class="mt-4 list-disc space-y-2 pl-5 text-sm">
            @forelse ($audit->issues ?? [] as $issue)
                <li>{{ $issue }}</li>
            @empty
                <li class="text-emerald-300">No critical issues found in this rule set.</li>
            @endforelse
        </ul>
    </section>
</x-layouts.app>
