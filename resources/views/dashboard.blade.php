<x-layouts.app :title="'Dashboard - SEO Toolkit'">
    <h1 class="text-2xl font-semibold">Dashboard</h1>
    <p class="mt-1 text-sm text-slate-400">Health overview across all tracked websites.</p>

    {{-- KPI Stats --}}
    <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-6">
        <a href="{{ route('websites.index') }}" class="rounded-xl border border-slate-800 bg-slate-900/70 p-4 hover:border-sky-400/60">
            <p class="text-xs text-slate-500 uppercase tracking-wide">Sites</p>
            <p class="mt-1 text-3xl font-bold">{{ $stats['sites'] }}</p>
        </a>

        <a href="{{ route('websites.index') }}" class="rounded-xl border border-slate-800 bg-slate-900/70 p-4 hover:border-sky-400/60">
            <p class="text-xs text-slate-500 uppercase tracking-wide">Keywords</p>
            <p class="mt-1 text-3xl font-bold">{{ $stats['keywords'] }}</p>
        </a>

        <a href="{{ route('websites.index') }}" class="rounded-xl border border-slate-800 bg-slate-900/70 p-4 hover:border-sky-400/60">
            <p class="text-xs text-slate-500 uppercase tracking-wide">Top 10</p>
            <p class="mt-1 text-3xl font-bold text-sky-300">{{ $stats['top_ten_keywords'] }}</p>
        </a>

        <a href="{{ route('alerts.index') }}" class="rounded-xl border border-slate-800 bg-slate-900/70 p-4 hover:border-sky-400/60">
            <p class="text-xs text-slate-500 uppercase tracking-wide">Open Alerts</p>
            <p class="mt-1 text-3xl font-bold {{ $stats['open_alerts'] > 0 ? 'text-red-400' : 'text-slate-100' }}">
                {{ $stats['open_alerts'] }}
            </p>
        </a>

        <a href="{{ route('websites.index') }}" class="rounded-xl border border-slate-800 bg-slate-900/70 p-4 hover:border-sky-400/60">
            <p class="text-xs text-slate-500 uppercase tracking-wide">Uptime</p>
            @if ($stats['uptime_rate'] === null)
                <p class="mt-1 text-3xl font-bold text-slate-500">—</p>
            @else
                <p class="mt-1 text-3xl font-bold {{ $stats['uptime_rate'] >= 99 ? 'text-emerald-400' : ($stats['uptime_rate'] >= 95 ? 'text-amber-400' : 'text-red-400') }}">
                    {{ $stats['uptime_rate'] }}%
                </p>
            @endif
        </a>

        <a href="{{ route('audits.index') }}" class="rounded-xl border border-slate-800 bg-slate-900/70 p-4 hover:border-sky-400/60">
            <p class="text-xs text-slate-500 uppercase tracking-wide">Audits</p>
            <p class="mt-1 text-3xl font-bold">{{ $stats['audit_count'] }}</p>
        </a>
    </div>

    {{-- Website Health Cards --}}
    <h2 class="mt-10 text-lg font-semibold">Websites</h2>

    @if ($websites->isEmpty())
        <div class="mt-4 rounded-xl border border-dashed border-slate-700 bg-slate-900/40 p-8 text-center">
            <p class="text-slate-400">No websites tracked yet.</p>
            <a href="{{ route('websites.index') }}" class="mt-3 inline-block text-sm text-sky-400 hover:text-sky-300">Add your first website →</a>
        </div>
    @else
        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($websites as $website)
                <div class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <h3 class="truncate font-medium">{{ $website->name }}</h3>
                            <p class="mt-0.5 truncate text-xs text-slate-500">{{ $website->base_url }}</p>
                        </div>
                        {{-- Uptime dot --}}
                        @if ($website->latestUptimeCheck)
                            <span class="mt-1 size-2.5 shrink-0 rounded-full {{ $website->latestUptimeCheck->is_up ? 'bg-emerald-400' : 'bg-red-400' }}"></span>
                        @else
                            <span class="mt-1 size-2.5 shrink-0 rounded-full bg-slate-600"></span>
                        @endif
                    </div>

                    <div class="mt-3 flex items-center gap-4 text-xs text-slate-400">
                        <span>
                            @if ($website->latestSeoAudit)
                                Score: <span class="font-semibold text-slate-200">{{ $website->latestSeoAudit->score ?? '—' }}</span>
                            @else
                                <span class="text-slate-600">No audit yet</span>
                            @endif
                        </span>
                        <span>{{ $website->keywords->count() }} keywords</span>
                    </div>

                    <a href="{{ route('websites.show', $website) }}" class="mt-3 inline-block text-xs text-sky-400 hover:text-sky-300">View →</a>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Recent Activity --}}
    <div class="mt-10 grid gap-6 lg:grid-cols-2">

        {{-- Recent Audits --}}
        <div>
            <h2 class="text-lg font-semibold">Recent Audits</h2>
            @if ($recentAudits->isEmpty())
                <p class="mt-3 text-sm text-slate-600">No audits yet.</p>
            @else
                <div class="mt-3 overflow-hidden rounded-xl border border-slate-800">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-800 text-xs text-slate-500">
                                <th class="px-4 py-2 text-left font-medium">Website</th>
                                <th class="px-4 py-2 text-left font-medium">Score</th>
                                <th class="px-4 py-2 text-left font-medium">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @foreach ($recentAudits as $audit)
                                <tr class="hover:bg-slate-800/30">
                                    <td class="px-4 py-2">
                                        <a href="{{ route('audits.show', $audit) }}" class="text-sky-400 hover:text-sky-300">
                                            {{ $audit->website->name ?? '—' }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2 text-slate-300">{{ $audit->score ?? '—' }}</td>
                                    <td class="px-4 py-2 text-slate-500">{{ $audit->audited_at?->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Recent Uptime Checks --}}
        <div>
            <h2 class="text-lg font-semibold">Recent Uptime Checks</h2>
            @if ($recentUptimeChecks->isEmpty())
                <p class="mt-3 text-sm text-slate-600">No uptime checks yet.</p>
            @else
                <div class="mt-3 overflow-hidden rounded-xl border border-slate-800">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-800 text-xs text-slate-500">
                                <th class="px-4 py-2 text-left font-medium">Website</th>
                                <th class="px-4 py-2 text-left font-medium">Status</th>
                                <th class="px-4 py-2 text-left font-medium">Checked</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @foreach ($recentUptimeChecks as $check)
                                <tr class="hover:bg-slate-800/30">
                                    <td class="px-4 py-2 text-slate-300">{{ $check->website->name ?? '—' }}</td>
                                    <td class="px-4 py-2">
                                        @if ($check->is_up)
                                            <span class="text-emerald-400 font-medium">UP</span>
                                        @else
                                            <span class="text-red-400 font-medium">DOWN</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-slate-500">{{ $check->checked_at?->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>
</x-layouts.app>
