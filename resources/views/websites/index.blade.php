<x-layouts.app :title="'Websites - SEO Toolkit'">
    <h1 class="text-2xl font-semibold">Websites</h1>
    <p class="mt-1 text-sm text-slate-400">All tracked properties for SEO monitoring.</p>

    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($websites as $website)
            <a href="{{ route('websites.show', $website) }}" class="rounded-xl border border-slate-800 bg-slate-900/70 p-4 hover:border-sky-400/60">
                <h2 class="text-lg font-medium">{{ $website->name }}</h2>
                <p class="mt-1 text-sm text-slate-400">{{ $website->base_url }}</p>
                <p class="mt-3 text-xs text-slate-500">Updated {{ $website->updated_at->diffForHumans() }}</p>
            </a>
        @endforeach
    </div>

    <div class="mt-6">{{ $websites->links() }}</div>
</x-layouts.app>
