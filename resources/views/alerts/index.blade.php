<x-layouts.app :title="'SEO Alerts - SEO Toolkit'">
    <h1 class="text-2xl font-semibold">SEO Alerts</h1>
    <p class="mt-1 text-sm text-slate-400">Traffic drops, indexation issues, and orphan page warnings.</p>

    <section class="mt-6 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
        <div class="grid gap-3 md:grid-cols-4">
        <form method="get" class="contents">
            <select name="website_id" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                <option value="">All websites</option>
                @foreach ($websites as $website)
                    <option value="{{ $website->id }}" @selected((string) request('website_id') === (string) $website->id)>{{ $website->name }}</option>
                @endforeach
            </select>
            <select name="severity" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                <option value="">Any severity</option>
                @foreach (['low','medium','high'] as $severity)
                    <option value="{{ $severity }}" @selected(request('severity') === $severity)>{{ ucfirst($severity) }}</option>
                @endforeach
            </select>
            <label class="flex items-center gap-2 rounded-md border border-slate-700 px-3 py-2 text-sm"><input type="checkbox" name="open_only" value="1" @checked(request('open_only'))>Open only</label>
            <button class="rounded-md border border-slate-700 px-3 py-2 text-sm hover:border-sky-400">Filter</button>
        </form>
        <form action="{{ route('alerts.evaluate') }}" method="post">
            @csrf
            <button class="rounded-md bg-orange-400 px-3 py-2 text-sm font-medium text-slate-950 hover:bg-orange-300">Run evaluation</button>
        </form>
        </div>
    </section>

    <section class="mt-6 overflow-x-auto rounded-xl border border-slate-800 bg-slate-900/70">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-left text-slate-400">
                    <th class="px-3 py-3">Detected</th>
                    <th class="px-3 py-3">Website</th>
                    <th class="px-3 py-3">Type</th>
                    <th class="px-3 py-3">Severity</th>
                    <th class="px-3 py-3">Message</th>
                    <th class="px-3 py-3">State</th>
                    <th class="px-3 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($alerts as $alert)
                    <tr class="border-b border-slate-900/70">
                        <td class="px-3 py-3">{{ $alert->detected_at->diffForHumans() }}</td>
                        <td class="px-3 py-3">{{ $alert->website->name }}</td>
                        <td class="px-3 py-3">{{ $alert->type }}</td>
                        <td class="px-3 py-3 capitalize">{{ $alert->severity }}</td>
                        <td class="px-3 py-3">{{ $alert->message }}</td>
                        <td class="px-3 py-3">{{ $alert->resolved_at ? 'resolved' : 'open' }}</td>
                        <td class="px-3 py-3">
                            @if (! $alert->resolved_at)
                                <form action="{{ route('alerts.resolve', $alert) }}" method="post">
                                    @csrf
                                    <button class="rounded border border-emerald-500/50 px-2 py-1 text-xs text-emerald-300">Resolve</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-3 py-5 text-slate-500">No alerts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <div class="mt-6">{{ $alerts->links() }}</div>
</x-layouts.app>
