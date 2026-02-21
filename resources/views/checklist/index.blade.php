<x-layouts.app :title="'SEO Checklist - SEO Toolkit'">
    <h1 class="text-3xl font-semibold tracking-tight">Get Your Site in Shape</h1>
    <p class="mt-2 text-sm text-slate-400">Action checklist aligned with Crawlability, On-Page, Technical, and International SEO.</p>

    <section class="mt-6 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
        <form method="get" class="flex gap-2">
            <select name="website_id" class="w-full rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" onchange="this.form.submit()">
                @foreach ($websites as $website)
                    <option value="{{ $website->id }}" @selected($selectedWebsite && $selectedWebsite->id === $website->id)>{{ $website->name }}</option>
                @endforeach
            </select>
            <button class="rounded-md border border-slate-700 px-3 py-2 text-sm hover:border-sky-400">Load</button>
        </form>
    </section>

    @if ($selectedWebsite && $checklist)
        <section class="mt-6 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold">Turn Checklist Into Tasks</h2>
                    <p class="text-sm text-slate-400">Create actionable tasks from all `warn` and `fail` checklist items.</p>
                </div>
                <form action="{{ route('checklist.tasks.generate') }}" method="post">
                    @csrf
                    <input type="hidden" name="website_id" value="{{ $selectedWebsite->id }}">
                    <button class="rounded-md bg-orange-400 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-orange-300">Generate tasks</button>
                </form>
            </div>
        </section>

        <section class="mt-6 grid gap-5">
            @php
                $groups = [
                    'crawlability' => ['title' => 'Crawlability & Indexability', 'bg' => 'bg-orange-100/95', 'text' => 'text-slate-900'],
                    'onpage' => ['title' => 'On-Page SEO', 'bg' => 'bg-amber-100/95', 'text' => 'text-slate-900'],
                    'technical' => ['title' => 'Technical SEO', 'bg' => 'bg-lime-100/95', 'text' => 'text-slate-900'],
                    'international' => ['title' => 'International SEO', 'bg' => 'bg-sky-100/95', 'text' => 'text-slate-900'],
                ];
                $statusStyles = [
                    'pass' => 'bg-emerald-600 text-white',
                    'warn' => 'bg-amber-500 text-slate-950',
                    'fail' => 'bg-red-600 text-white',
                ];
            @endphp

            @foreach ($groups as $key => $group)
                <article class="{{ $group['bg'] }} {{ $group['text'] }} rounded-2xl border border-black/5 p-6 shadow-sm">
                    <h2 class="text-3xl font-semibold tracking-tight">{{ $group['title'] }}</h2>
                    <ul class="mt-5 space-y-3">
                        @foreach ($checklist[$key] as $item)
                            <li class="rounded-lg bg-white/60 px-4 py-3">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="font-medium">{{ $item['label'] }}</p>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold uppercase {{ $statusStyles[$item['status']] ?? 'bg-slate-600 text-white' }}">{{ $item['status'] }}</span>
                                </div>
                                <p class="mt-1 text-sm text-slate-700">{{ $item['detail'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </section>

        <section class="mt-6 rounded-xl border border-slate-800 bg-slate-900/70 p-4 text-sm text-slate-300">
            <p>Latest crawl run: {{ $checklist['meta']['latest_run_id'] ? '#'.$checklist['meta']['latest_run_id'] : 'none' }}</p>
            <p>Latest audit: {{ $checklist['meta']['latest_audit_id'] ? '#'.$checklist['meta']['latest_audit_id'] : 'none' }}</p>
            <p>Pages scanned: {{ $checklist['meta']['pages_scanned'] }} · Noindex pages: {{ $checklist['meta']['noindex_pages'] }}</p>
        </section>

        <section class="mt-6 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h2 class="text-lg font-semibold">Checklist Tasks</h2>
            <div class="mt-3 space-y-2">
                @forelse ($tasks as $task)
                    <article class="rounded-lg border border-slate-800 bg-slate-950/60 p-3">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-medium">{{ $task->title }}</p>
                                <p class="text-xs text-slate-500 uppercase">{{ $task->section }} · {{ $task->priority }}</p>
                                @if ($task->details)
                                    <p class="mt-1 text-sm text-slate-300">{{ $task->details }}</p>
                                @endif
                                <p class="mt-1 text-xs text-slate-500">Due: {{ optional($task->due_date)->toDateString() ?? 'n/a' }}</p>
                            </div>
                            <div class="text-right">
                                @if ($task->status === 'open')
                                    <form action="{{ route('checklist.tasks.complete', $task) }}" method="post">
                                        @csrf
                                        <button class="rounded-md border border-emerald-500/60 px-2 py-1 text-xs text-emerald-300 hover:bg-emerald-500/10">Mark done</button>
                                    </form>
                                @else
                                    <span class="rounded-full bg-emerald-600 px-2 py-1 text-xs font-semibold uppercase text-white">done</span>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-slate-500">No tasks generated yet.</p>
                @endforelse
            </div>
        </section>
    @endif
</x-layouts.app>
