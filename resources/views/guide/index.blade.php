@php
    $chipClasses = [
        'sky' => 'border-sky-400/40 bg-sky-500/10 text-sky-300',
        'emerald' => 'border-emerald-400/40 bg-emerald-500/10 text-emerald-300',
        'amber' => 'border-amber-400/40 bg-amber-500/10 text-amber-300',
        'slate' => 'border-slate-700 bg-slate-800/60 text-slate-400',
    ];
@endphp
<x-layouts.app :title="'Guide - SEO Toolkit'">
    <h1 class="text-2xl font-semibold">How to actually run this thing</h1>
    <p class="mt-1 max-w-2xl text-sm text-slate-400">One page, organized by what to do first, how data actually gets in, and what every page is for. Nothing here needs Google Cloud Console.</p>

    <section class="mt-6 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
        <h2 class="text-lg font-semibold">Do these in order, once</h2>
        <p class="mt-1 text-xs text-slate-500">After that, everything is fair game in any order — several pages just stay empty until an earlier step feeds them data.</p>
        <ol class="mt-4 space-y-4">
            @foreach ($steps as $i => $step)
                <li class="flex gap-4 border-t border-slate-800 pt-4 first:border-t-0 first:pt-0">
                    <span class="text-lg font-semibold text-sky-400">{{ $i + 1 }}</span>
                    <div>
                        <p class="font-medium text-slate-200">{{ $step['title'] }}</p>
                        <p class="mt-1 text-sm text-slate-400">{{ $step['desc'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </section>

    <section class="mt-6 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
        <h2 class="text-lg font-semibold">How data gets into each page</h2>
        <p class="mt-1 text-xs text-slate-500">Scan by chip below if a page looks empty — it's usually just waiting on an earlier step.</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            @foreach ($legend as $item)
                <div class="rounded-md border border-slate-800 bg-slate-950/50 p-3">
                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs {{ $chipClasses[$item['color']] }}">{{ $item['label'] }}</span>
                    <p class="mt-2 text-sm text-slate-400">{{ $item['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mt-6">
        <h2 class="text-lg font-semibold">Every page, what it's for</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($modules as $module)
                <a href="{{ route($module['route']) }}" class="flex flex-col gap-2 rounded-xl border border-slate-800 bg-slate-900/70 p-4 hover:border-sky-400">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="font-semibold text-slate-100">{{ $module['title'] }}</h3>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($module['chips'] as [$label, $color])
                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] {{ $chipClasses[$color] }}">{{ $label }}</span>
                        @endforeach
                    </div>
                    <p class="text-sm text-slate-400">{{ $module['desc'] }}</p>
                </a>
            @endforeach
        </div>
    </section>

    <section class="mt-6 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
        <h2 class="text-lg font-semibold">What runs on its own</h2>
        <p class="mt-1 text-xs text-slate-500">Three jobs run in the background without you touching anything. Everything else above needs a click.</p>
        <div class="mt-4 grid gap-2">
            @foreach ($automation as $job)
                <div class="flex items-center justify-between gap-3 rounded-md border border-slate-800 bg-slate-950/50 p-3">
                    <div>
                        <p class="text-sm font-medium text-slate-200">{{ $job['name'] }}</p>
                        <p class="text-xs text-slate-500">{{ $job['desc'] }}</p>
                    </div>
                    <span class="whitespace-nowrap text-xs text-sky-300">{{ $job['cadence'] }}</span>
                </div>
            @endforeach
        </div>
    </section>
</x-layouts.app>
