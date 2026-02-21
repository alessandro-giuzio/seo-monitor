<x-layouts.app :title="$website->name . ' - SEO Toolkit'">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold">{{ $website->name }}</h1>
            <p class="text-sm text-slate-400">{{ $website->base_url }}</p>
        </div>
        <a href="{{ route('dashboard') }}" class="rounded-md border border-slate-700 px-3 py-1.5 text-sm hover:border-sky-400 hover:text-sky-300">Back to dashboard</a>
    </div>

    <section class="mt-6 grid gap-6 lg:grid-cols-2">
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h2 class="text-lg font-semibold">Update Website</h2>
            <form action="{{ route('websites.update', $website) }}" method="post" class="mt-4 grid gap-3">
                @csrf
                @method('PUT')
                <input name="name" value="{{ old('name', $website->name) }}" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" required>
                <input name="base_url" value="{{ old('base_url', $website->base_url) }}" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" required>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="industry" value="{{ old('industry', $website->industry) }}" placeholder="Industry" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                    <input name="target_country" value="{{ old('target_country', $website->target_country) }}" maxlength="2" placeholder="US" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="gsc_property" value="{{ old('gsc_property', $website->gsc_property) }}" placeholder="sc-domain:example.com" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                    <input name="alert_email" value="{{ old('alert_email', $website->alert_email) }}" placeholder="alerts@example.com" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                </div>
                <input type="number" min="1" max="168" name="crawl_frequency_hours" value="{{ old('crawl_frequency_hours', $website->crawl_frequency_hours ?? 24) }}" placeholder="Crawl every X hours" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                <textarea name="notes" rows="3" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">{{ old('notes', $website->notes) }}</textarea>
                <button class="rounded-md bg-sky-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-sky-400">Save changes</button>
            </form>
        </article>

        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h2 class="text-lg font-semibold">Latest Monitoring</h2>
            <div class="mt-4 grid gap-3 text-sm">
                @if ($website->latestUptimeCheck)
                    <div class="rounded-md border border-slate-800 bg-slate-950/50 p-3">
                        <p class="text-slate-400">Uptime</p>
                        <p class="mt-1 {{ $website->latestUptimeCheck->is_up ? 'text-emerald-300' : 'text-red-300' }}">{{ $website->latestUptimeCheck->is_up ? 'UP' : 'DOWN' }}</p>
                        <p class="text-xs text-slate-500">{{ $website->latestUptimeCheck->status_code }} · {{ $website->latestUptimeCheck->response_time_ms }}ms · {{ $website->latestUptimeCheck->checked_at }}</p>
                    </div>
                @endif

                @if ($website->latestSeoAudit)
                    <div class="rounded-md border border-slate-800 bg-slate-950/50 p-3">
                        <p class="text-slate-400">SEO Audit</p>
                        <p class="mt-1 capitalize">{{ $website->latestSeoAudit->status }} · Score {{ $website->latestSeoAudit->score }}</p>
                        <a href="{{ route('audits.show', $website->latestSeoAudit) }}" class="text-xs text-sky-300 hover:text-sky-200">Open audit</a>
                    </div>
                @endif

                @if ($website->crawlRuns->isNotEmpty())
                    <div class="rounded-md border border-slate-800 bg-slate-950/50 p-3">
                        <p class="text-slate-400">Technical Crawl</p>
                        <p class="mt-1 capitalize">Last run: {{ $website->crawlRuns->first()->started_at->diffForHumans() }}</p>
                        <p class="text-xs text-slate-500">Next due: {{ optional($website->next_crawl_at)->diffForHumans() ?? 'not set' }}</p>
                    </div>
                @endif

                @if ($website->seoAlerts->isNotEmpty())
                    <div class="rounded-md border border-slate-800 bg-slate-950/50 p-3">
                        <p class="text-slate-400">Open Alerts</p>
                        <p class="mt-1">{{ $website->seoAlerts->count() }}</p>
                        <a href="{{ route('alerts.index', ['website_id' => $website->id, 'open_only' => 1]) }}" class="text-xs text-sky-300 hover:text-sky-200">View alerts</a>
                    </div>
                @endif
            </div>
        </article>
    </section>

    <section class="mt-6 grid gap-6 lg:grid-cols-3">
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5 lg:col-span-2">
            <h2 class="text-lg font-semibold">Keywords</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="px-2 py-2">Keyword</th>
                        <th class="px-2 py-2">Engine</th>
                        <th class="px-2 py-2">Device</th>
                        <th class="px-2 py-2">Latest Position</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($website->keywords as $keyword)
                        <tr class="border-b border-slate-900/70">
                            <td class="px-2 py-2">{{ $keyword->term }}</td>
                            <td class="px-2 py-2">{{ $keyword->search_engine }}</td>
                            <td class="px-2 py-2 capitalize">{{ $keyword->device }}</td>
                            <td class="px-2 py-2">{{ $keyword->latestSnapshot?->position ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-2 py-4 text-slate-500">No keywords yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h2 class="text-lg font-semibold">Recent Uptime Checks</h2>
            <div class="mt-4 space-y-2 text-sm">
                @forelse ($website->uptimeChecks as $check)
                    <div class="rounded-md border border-slate-800 p-2">
                        <p class="{{ $check->is_up ? 'text-emerald-300' : 'text-red-300' }}">{{ $check->is_up ? 'UP' : 'DOWN' }}</p>
                        <p class="text-xs text-slate-500">{{ $check->status_code }} · {{ $check->response_time_ms }}ms · {{ $check->checked_at }}</p>
                    </div>
                @empty
                    <p class="text-slate-500">No checks yet.</p>
                @endforelse
            </div>
        </article>
    </section>
</x-layouts.app>
