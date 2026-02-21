<x-layouts.app :title="'Crawl Run #' . $run->id">
    <h1 class="text-2xl font-semibold">Crawl Run #{{ $run->id }}</h1>
    <p class="mt-1 text-sm text-slate-400">{{ $run->website->name }} · {{ $run->started_at }} · {{ $run->status }}</p>

    <section class="mt-6 grid gap-6 lg:grid-cols-2">
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h2 class="text-lg font-semibold">Summary</h2>
            <pre class="mt-3 overflow-auto rounded-md border border-slate-800 bg-slate-950/70 p-3 text-xs">{{ json_encode($run->summary, JSON_PRETTY_PRINT) }}</pre>
        </article>

        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h2 class="text-lg font-semibold">Internal Link Opportunities</h2>
            <div class="mt-3 space-y-2 text-sm">
                @forelse ($run->linkOpportunities as $opportunity)
                    <div class="rounded-md border border-slate-800 p-3">
                        <p class="text-xs text-slate-500">Score {{ $opportunity->priority_score }}</p>
                        <p class="mt-1">Source: {{ $opportunity->source_url }}</p>
                        <p>Target: {{ $opportunity->target_url }}</p>
                        <p class="text-xs text-slate-500">{{ $opportunity->reason }}</p>
                    </div>
                @empty
                    <p class="text-slate-500">No opportunities generated.</p>
                @endforelse
            </div>
        </article>
    </section>
</x-layouts.app>
