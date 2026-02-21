<x-layouts.app :title="'SEO Toolkit Dashboard'">
    <section class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
            <p class="text-xs uppercase tracking-wide text-slate-400">Websites</p>
            <p class="mt-2 text-3xl font-semibold">{{ $stats['sites'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
            <p class="text-xs uppercase tracking-wide text-slate-400">Tracked Keywords</p>
            <p class="mt-2 text-3xl font-semibold">{{ $stats['keywords'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
            <p class="text-xs uppercase tracking-wide text-slate-400">Top 10 Keywords</p>
            <p class="mt-2 text-3xl font-semibold">{{ $stats['top_ten_keywords'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
            <p class="text-xs uppercase tracking-wide text-slate-400">Audits Logged</p>
            <p class="mt-2 text-3xl font-semibold">{{ $stats['audit_count'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
            <p class="text-xs uppercase tracking-wide text-slate-400">Uptime Health</p>
            <p class="mt-2 text-3xl font-semibold">{{ $stats['uptime_rate'] !== null ? $stats['uptime_rate'].'%' : 'n/a' }}</p>
        </article>
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
            <p class="text-xs uppercase tracking-wide text-slate-400">Keyword Ideas</p>
            <p class="mt-2 text-3xl font-semibold">{{ $stats['keyword_ideas'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
            <p class="text-xs uppercase tracking-wide text-slate-400">Competitors</p>
            <p class="mt-2 text-3xl font-semibold">{{ $stats['competitors'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
            <p class="text-xs uppercase tracking-wide text-slate-400">Backlinks</p>
            <p class="mt-2 text-3xl font-semibold">{{ $stats['backlinks'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
            <p class="text-xs uppercase tracking-wide text-slate-400">GSC Rows</p>
            <p class="mt-2 text-3xl font-semibold">{{ $stats['gsc_rows'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
            <p class="text-xs uppercase tracking-wide text-slate-400">Crawled Pages</p>
            <p class="mt-2 text-3xl font-semibold">{{ $stats['crawl_pages'] }}</p>
        </article>
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-4">
            <p class="text-xs uppercase tracking-wide text-slate-400">Open Alerts</p>
            <p class="mt-2 text-3xl font-semibold">{{ $stats['open_alerts'] }}</p>
        </article>
    </section>

    <section class="mb-8 grid gap-6 lg:grid-cols-2">
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h2 class="text-lg font-semibold">Add Website</h2>
            <form action="{{ route('websites.store') }}" method="post" class="mt-4 grid gap-3">
                @csrf
                <input name="name" placeholder="Name" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" required>
                <input name="base_url" placeholder="https://example.com" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" required>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="industry" placeholder="Industry" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                    <input name="target_country" placeholder="Country (US)" maxlength="2" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <input name="gsc_property" placeholder="sc-domain:example.com" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                    <input name="alert_email" placeholder="alerts@example.com" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                </div>
                <input type="number" min="1" max="168" name="crawl_frequency_hours" value="24" placeholder="Crawl every X hours" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                <textarea name="notes" rows="2" placeholder="Notes" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm"></textarea>
                <button class="rounded-md bg-sky-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-sky-400">Add website</button>
            </form>
        </article>

        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h2 class="text-lg font-semibold">Run On-Page Audit</h2>
            <form action="{{ route('audits.store') }}" method="post" class="mt-4 grid gap-3">
                @csrf
                <select name="website_id" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                    <option value="">No linked website</option>
                    @foreach ($websites as $website)
                        <option value="{{ $website->id }}">{{ $website->name }}</option>
                    @endforeach
                </select>
                <input name="url" placeholder="https://example.com/page" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" required>
                <textarea name="raw_html" rows="4" placeholder="Optional: paste HTML to audit without live fetch" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm"></textarea>
                <button class="rounded-md bg-orange-400 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-orange-300">Run audit</button>
            </form>
        </article>
    </section>

    <section class="mb-8 grid gap-6">
        @forelse ($websites as $website)
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold">{{ $website->name }}</h3>
                        <p class="text-sm text-slate-400">{{ $website->base_url }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('websites.show', $website) }}" class="rounded-md border border-slate-700 px-3 py-1.5 text-sm hover:border-sky-400 hover:text-sky-300">Details</a>
                        <form action="{{ route('websites.destroy', $website) }}" method="post" onsubmit="return confirm('Delete website and all its tracking data?')">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-md border border-red-500/50 px-3 py-1.5 text-sm text-red-300 hover:bg-red-500/10">Delete</button>
                        </form>
                    </div>
                </div>

                <div class="mt-4 grid gap-4 lg:grid-cols-3">
                    <div class="rounded-lg border border-slate-800 bg-slate-950/60 p-3">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Latest Uptime</p>
                        @if ($website->latestUptimeCheck)
                            <p class="mt-1 text-sm">
                                <span class="{{ $website->latestUptimeCheck->is_up ? 'text-emerald-300' : 'text-red-300' }}">
                                    {{ $website->latestUptimeCheck->is_up ? 'UP' : 'DOWN' }}
                                </span>
                                · {{ $website->latestUptimeCheck->status_code ?? '-' }} · {{ $website->latestUptimeCheck->response_time_ms ?? '-' }}ms
                            </p>
                        @else
                            <p class="mt-1 text-sm text-slate-500">No checks yet.</p>
                        @endif
                    </div>

                    <div class="rounded-lg border border-slate-800 bg-slate-950/60 p-3">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Latest SEO Audit</p>
                        @if ($website->latestSeoAudit)
                            <p class="mt-1 text-sm capitalize text-slate-200">{{ $website->latestSeoAudit->status }} · Score {{ $website->latestSeoAudit->score }}</p>
                        @else
                            <p class="mt-1 text-sm text-slate-500">No audits yet.</p>
                        @endif
                    </div>

                    <div class="rounded-lg border border-slate-800 bg-slate-950/60 p-3">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Tracked Keywords</p>
                        <p class="mt-1 text-sm text-slate-200">{{ $website->keywords->count() }}</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    <form action="{{ route('keywords.store', $website) }}" method="post" class="rounded-lg border border-slate-800 bg-slate-950/60 p-3 grid gap-2">
                        @csrf
                        <p class="text-sm font-medium">Add keyword</p>
                        <input name="term" placeholder="seo tools for ecommerce" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" required>
                        <input name="target_url" placeholder="https://example.com/page" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                        <div class="grid grid-cols-2 gap-2">
                            <input name="search_engine" value="Google" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" required>
                            <input name="location" placeholder="US" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <select name="device" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                                <option value="desktop">Desktop</option>
                                <option value="mobile">Mobile</option>
                            </select>
                            <select name="priority" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                                <option value="1">Priority 1</option>
                                <option value="2" selected>Priority 2</option>
                                <option value="3">Priority 3</option>
                            </select>
                        </div>
                        <button class="rounded-md bg-sky-500 px-3 py-2 text-sm font-medium text-slate-950 hover:bg-sky-400">Save keyword</button>
                    </form>

                    <form action="{{ route('uptime.store', $website) }}" method="post" class="rounded-lg border border-slate-800 bg-slate-950/60 p-3 grid gap-2">
                        @csrf
                        <p class="text-sm font-medium">Log uptime check</p>
                        <input type="datetime-local" name="checked_at" value="{{ now()->format('Y-m-d\\TH:i') }}" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" required>
                        <div class="grid grid-cols-2 gap-2">
                            <input name="status_code" type="number" min="100" max="599" placeholder="200" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                            <input name="response_time_ms" type="number" min="1" placeholder="350" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                        </div>
                        <select name="is_up" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                            <option value="1">Up</option>
                            <option value="0">Down</option>
                        </select>
                        <input name="notes" placeholder="Optional note" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                        <button class="rounded-md bg-emerald-400 px-3 py-2 text-sm font-medium text-slate-950 hover:bg-emerald-300">Log check</button>
                    </form>
                </div>

                @if ($website->keywords->isNotEmpty())
                    <div class="mt-5 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                            <tr class="border-b border-slate-800 text-left text-slate-400">
                                <th class="px-2 py-2">Keyword</th>
                                <th class="px-2 py-2">Engine</th>
                                <th class="px-2 py-2">Device</th>
                                <th class="px-2 py-2">Latest Position</th>
                                <th class="px-2 py-2">Snapshot</th>
                                <th class="px-2 py-2">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($website->keywords as $keyword)
                                <tr class="border-b border-slate-900/70 align-top">
                                    <td class="px-2 py-2">
                                        <p class="font-medium">{{ $keyword->term }}</p>
                                        @if ($keyword->target_url)
                                            <p class="text-xs text-slate-500">{{ $keyword->target_url }}</p>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2">{{ $keyword->search_engine }}</td>
                                    <td class="px-2 py-2 capitalize">{{ $keyword->device }}</td>
                                    <td class="px-2 py-2">{{ $keyword->latestSnapshot?->position ?? '-' }}</td>
                                    <td class="px-2 py-2">
                                        <form action="{{ route('rankings.store', $keyword) }}" method="post" class="grid gap-1 sm:grid-cols-2">
                                            @csrf
                                            <input type="datetime-local" name="checked_at" value="{{ now()->format('Y-m-d\\TH:i') }}" class="rounded-md border border-slate-700 bg-slate-950 px-2 py-1 text-xs" required>
                                            <input type="number" min="1" max="1000" name="position" placeholder="Pos" class="rounded-md border border-slate-700 bg-slate-950 px-2 py-1 text-xs">
                                            <input type="number" min="0" name="search_volume" placeholder="Volume" class="rounded-md border border-slate-700 bg-slate-950 px-2 py-1 text-xs">
                                            <input type="number" min="0" max="100" name="difficulty" placeholder="KD" class="rounded-md border border-slate-700 bg-slate-950 px-2 py-1 text-xs">
                                            <input name="serp_features" placeholder="featured snippet,faq" class="rounded-md border border-slate-700 bg-slate-950 px-2 py-1 text-xs sm:col-span-2">
                                            <button class="rounded-md bg-sky-500 px-2 py-1 text-xs font-medium text-slate-950 hover:bg-sky-400 sm:col-span-2">Add snapshot</button>
                                        </form>
                                    </td>
                                    <td class="px-2 py-2">
                                        <form action="{{ route('keywords.destroy', $keyword) }}" method="post" onsubmit="return confirm('Delete keyword and ranking history?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded border border-red-500/50 px-2 py-1 text-xs text-red-300">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>
        @empty
            <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-8 text-center text-slate-400">
                Add your first website to start tracking keywords, uptime, and SEO audits.
            </article>
        @endforelse
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h2 class="text-lg font-semibold">Recent Audits</h2>
            <div class="mt-3 space-y-2 text-sm">
                @forelse ($recentAudits as $audit)
                    <a href="{{ route('audits.show', $audit) }}" class="block rounded-md border border-slate-800 px-3 py-2 hover:border-sky-400/60">
                        <div class="flex items-center justify-between gap-2">
                            <span class="truncate">{{ $audit->url }}</span>
                            <span class="capitalize">{{ $audit->status }} · {{ $audit->score }}</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">{{ $audit->website?->name ?? 'Unlinked' }} · {{ $audit->audited_at->diffForHumans() }}</p>
                    </a>
                @empty
                    <p class="text-slate-500">No audits yet.</p>
                @endforelse
            </div>
        </article>

        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h2 class="text-lg font-semibold">Recent Uptime Checks</h2>
            <div class="mt-3 space-y-2 text-sm">
                @forelse ($recentUptimeChecks as $check)
                    <div class="rounded-md border border-slate-800 px-3 py-2">
                        <div class="flex items-center justify-between gap-2">
                            <span>{{ $check->website?->name ?? 'Unknown site' }}</span>
                            <span class="{{ $check->is_up ? 'text-emerald-300' : 'text-red-300' }}">{{ $check->is_up ? 'UP' : 'DOWN' }}</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">{{ $check->status_code ?? '-' }} · {{ $check->response_time_ms ?? '-' }}ms · {{ $check->checked_at->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-slate-500">No uptime checks yet.</p>
                @endforelse
            </div>
        </article>
    </section>
</x-layouts.app>
