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
        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5 lg:col-span-2" x-data="{ adding: false }">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold">Keywords</h2>
                <button type="button" @click="adding = !adding"
                        class="rounded-md border border-slate-700 px-3 py-1.5 text-xs hover:border-sky-400 hover:text-sky-300">
                    <span x-text="adding ? '✕ Cancel' : '+ Add keyword'"></span>
                </button>
            </div>

            <form x-show="adding" x-transition action="{{ route('keywords.store', $website) }}" method="post"
                  class="mt-4 grid gap-2 rounded-lg border border-slate-800 bg-slate-950/50 p-3 sm:grid-cols-2 lg:grid-cols-3">
                @csrf
                <input name="term" placeholder="Keyword" required
                       class="rounded-md border border-slate-700 bg-slate-900 px-2 py-1.5 text-sm focus:border-sky-500 focus:outline-none">
                <input name="target_url" placeholder="Target URL (optional)"
                       class="rounded-md border border-slate-700 bg-slate-900 px-2 py-1.5 text-sm focus:border-sky-500 focus:outline-none">
                <input name="search_engine" value="Google" placeholder="Search engine" required
                       class="rounded-md border border-slate-700 bg-slate-900 px-2 py-1.5 text-sm focus:border-sky-500 focus:outline-none">
                <input name="location" placeholder="Location (optional)"
                       class="rounded-md border border-slate-700 bg-slate-900 px-2 py-1.5 text-sm focus:border-sky-500 focus:outline-none">
                <select name="device" class="rounded-md border border-slate-700 bg-slate-900 px-2 py-1.5 text-sm focus:border-sky-500 focus:outline-none">
                    <option value="desktop">Desktop</option>
                    <option value="mobile">Mobile</option>
                </select>
                <select name="priority" class="rounded-md border border-slate-700 bg-slate-900 px-2 py-1.5 text-sm focus:border-sky-500 focus:outline-none">
                    <option value="1">Priority 1</option>
                    <option value="2" selected>Priority 2</option>
                    <option value="3">Priority 3</option>
                </select>
                <button type="submit" class="rounded-md bg-sky-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-sky-500 sm:col-span-2 lg:col-span-3">
                    Save keyword
                </button>
            </form>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="px-2 py-2">Keyword</th>
                        <th class="px-2 py-2">Engine</th>
                        <th class="px-2 py-2">Device</th>
                        <th class="px-2 py-2">Latest Position</th>
                        <th class="px-2 py-2"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($website->keywords as $keyword)
                        <tr class="border-b border-slate-900/70" x-data="{ editing: false, logging: false }">
                            <td colspan="5" class="p-0">
                                <div x-show="!editing" class="grid grid-cols-5 items-center gap-2 px-2 py-2">
                                    <span>{{ $keyword->term }}</span>
                                    <span>{{ $keyword->search_engine }}</span>
                                    <span class="capitalize">{{ $keyword->device }}</span>
                                    <span>{{ $keyword->latestSnapshot?->position ?? '-' }}</span>
                                    <span class="flex justify-end gap-2 text-xs">
                                        <button type="button" @click="logging = !logging" class="rounded border border-slate-700 px-2 py-1 hover:border-sky-400 hover:text-sky-300">Log ranking</button>
                                        <button type="button" @click="editing = true" class="rounded border border-slate-700 px-2 py-1 hover:border-sky-400 hover:text-sky-300">Edit</button>
                                        <form action="{{ route('keywords.destroy', $keyword) }}" method="post" onsubmit="return confirm('Delete keyword &quot;{{ $keyword->term }}&quot;?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded border border-red-500/50 px-2 py-1 text-red-300">Delete</button>
                                        </form>
                                    </span>
                                </div>

                                <form x-show="logging" x-transition action="{{ route('rankings.store', $keyword) }}" method="post"
                                      class="grid grid-cols-2 gap-2 border-t border-slate-800 bg-slate-950/50 px-2 py-2 sm:grid-cols-5">
                                    @csrf
                                    <label class="text-xs text-slate-400 sm:col-span-1">Checked at
                                        <input type="datetime-local" name="checked_at" value="{{ now()->format('Y-m-d\TH:i') }}" required
                                               class="mt-1 w-full rounded-md border border-slate-700 bg-slate-900 px-2 py-1 text-xs focus:border-sky-500 focus:outline-none">
                                    </label>
                                    <label class="text-xs text-slate-400">Position
                                        <input type="number" name="position" min="1" max="1000" placeholder="e.g. 4"
                                               class="mt-1 w-full rounded-md border border-slate-700 bg-slate-900 px-2 py-1 text-xs focus:border-sky-500 focus:outline-none">
                                    </label>
                                    <label class="text-xs text-slate-400">Search volume
                                        <input type="number" name="search_volume" min="0" placeholder="Optional"
                                               class="mt-1 w-full rounded-md border border-slate-700 bg-slate-900 px-2 py-1 text-xs focus:border-sky-500 focus:outline-none">
                                    </label>
                                    <label class="text-xs text-slate-400">Difficulty
                                        <input type="number" name="difficulty" min="0" max="100" placeholder="Optional"
                                               class="mt-1 w-full rounded-md border border-slate-700 bg-slate-900 px-2 py-1 text-xs focus:border-sky-500 focus:outline-none">
                                    </label>
                                    <label class="text-xs text-slate-400">SERP features
                                        <input name="serp_features" placeholder="featured snippet, sitelinks"
                                               class="mt-1 w-full rounded-md border border-slate-700 bg-slate-900 px-2 py-1 text-xs focus:border-sky-500 focus:outline-none">
                                    </label>
                                    <div class="flex items-end justify-end gap-2 sm:col-span-5">
                                        <button type="button" @click="logging = false" class="rounded border border-slate-700 px-2 py-1 text-xs hover:border-slate-500">Cancel</button>
                                        <button type="submit" class="rounded bg-sky-600 px-2 py-1 text-xs font-medium text-white hover:bg-sky-500">Save snapshot</button>
                                    </div>
                                </form>

                                <form x-show="editing" action="{{ route('keywords.update', $keyword) }}" method="post"
                                      class="grid grid-cols-2 gap-2 px-2 py-2 sm:grid-cols-5">
                                    @csrf
                                    @method('PUT')
                                    <input name="term" value="{{ $keyword->term }}" required
                                           class="rounded-md border border-slate-700 bg-slate-900 px-2 py-1 text-xs focus:border-sky-500 focus:outline-none">
                                    <input name="search_engine" value="{{ $keyword->search_engine }}" required
                                           class="rounded-md border border-slate-700 bg-slate-900 px-2 py-1 text-xs focus:border-sky-500 focus:outline-none">
                                    <select name="device" class="rounded-md border border-slate-700 bg-slate-900 px-2 py-1 text-xs focus:border-sky-500 focus:outline-none">
                                        <option value="desktop" @selected($keyword->device === 'desktop')>Desktop</option>
                                        <option value="mobile" @selected($keyword->device === 'mobile')>Mobile</option>
                                    </select>
                                    <select name="priority" class="rounded-md border border-slate-700 bg-slate-900 px-2 py-1 text-xs focus:border-sky-500 focus:outline-none">
                                        <option value="1" @selected($keyword->priority === 1)>Priority 1</option>
                                        <option value="2" @selected($keyword->priority === 2)>Priority 2</option>
                                        <option value="3" @selected($keyword->priority === 3)>Priority 3</option>
                                    </select>
                                    <input type="hidden" name="target_url" value="{{ $keyword->target_url }}">
                                    <input type="hidden" name="location" value="{{ $keyword->location }}">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="editing = false" class="rounded border border-slate-700 px-2 py-1 text-xs hover:border-slate-500">Cancel</button>
                                        <button type="submit" class="rounded bg-sky-600 px-2 py-1 text-xs font-medium text-white hover:bg-sky-500">Save</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-2 py-4 text-slate-500">No keywords yet. Use "+ Add keyword" above to track your first one.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="rounded-xl border border-slate-800 bg-slate-900/70 p-5" x-data="{ recording: false }">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold">Recent Uptime Checks</h2>
                <button type="button" @click="recording = !recording"
                        class="rounded-md border border-slate-700 px-3 py-1.5 text-xs hover:border-sky-400 hover:text-sky-300">
                    <span x-text="recording ? '✕ Cancel' : '+ Record check'"></span>
                </button>
            </div>

            <form x-show="recording" x-transition action="{{ route('uptime.store', $website) }}" method="post"
                  class="mt-4 grid gap-2 rounded-lg border border-slate-800 bg-slate-950/50 p-3">
                @csrf
                <label class="text-xs text-slate-400">Checked at
                    <input type="datetime-local" name="checked_at" value="{{ now()->format('Y-m-d\TH:i') }}" required
                           class="mt-1 w-full rounded-md border border-slate-700 bg-slate-900 px-2 py-1.5 text-sm focus:border-sky-500 focus:outline-none">
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="text-xs text-slate-400">Status
                        <select name="is_up" class="mt-1 w-full rounded-md border border-slate-700 bg-slate-900 px-2 py-1.5 text-sm focus:border-sky-500 focus:outline-none">
                            <option value="1">Up</option>
                            <option value="0">Down</option>
                        </select>
                    </label>
                    <label class="text-xs text-slate-400">HTTP status code
                        <input type="number" name="status_code" min="100" max="599" placeholder="200"
                               class="mt-1 w-full rounded-md border border-slate-700 bg-slate-900 px-2 py-1.5 text-sm focus:border-sky-500 focus:outline-none">
                    </label>
                </div>
                <label class="text-xs text-slate-400">Response time (ms)
                    <input type="number" name="response_time_ms" min="1" placeholder="240"
                           class="mt-1 w-full rounded-md border border-slate-700 bg-slate-900 px-2 py-1.5 text-sm focus:border-sky-500 focus:outline-none">
                </label>
                <label class="text-xs text-slate-400">Notes
                    <input name="notes" placeholder="Optional"
                           class="mt-1 w-full rounded-md border border-slate-700 bg-slate-900 px-2 py-1.5 text-sm focus:border-sky-500 focus:outline-none">
                </label>
                <button type="submit" class="rounded-md bg-sky-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-sky-500">
                    Save check
                </button>
            </form>

            <div class="mt-4 space-y-2 text-sm">
                @forelse ($website->uptimeChecks as $check)
                    <div class="rounded-md border border-slate-800 p-2">
                        <p class="{{ $check->is_up ? 'text-emerald-300' : 'text-red-300' }}">{{ $check->is_up ? 'UP' : 'DOWN' }}</p>
                        <p class="text-xs text-slate-500">{{ $check->status_code }} · {{ $check->response_time_ms }}ms · {{ $check->checked_at }}</p>
                    </div>
                @empty
                    <p class="text-slate-500">No checks yet. Use "+ Record check" above to log your first one.</p>
                @endforelse
            </div>
        </article>
    </section>
</x-layouts.app>
