<x-layouts.app :title="'SEO Change Log - SEO Toolkit'">
    <h1 class="text-2xl font-semibold">SEO Change Log</h1>
    <p class="mt-1 text-sm text-slate-400">Track SEO edits and correlate them with performance changes.</p>

    <section class="mt-6 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
        <form method="get" class="flex gap-2">
            <select name="website_id" class="w-full rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" onchange="this.form.submit()">
                @foreach ($websites as $website)
                    <option value="{{ $website->id }}" @selected($selectedWebsite && $selectedWebsite->id === $website->id)>{{ $website->name }}</option>
                @endforeach
            </select>
            <button class="rounded-md border border-slate-700 px-3 py-2 text-sm">Load</button>
        </form>
    </section>

    @if ($selectedWebsite)
        <section class="mt-6 rounded-xl border border-slate-800 bg-slate-900/70 p-5">
            <h2 class="text-lg font-semibold">Add Change</h2>
            <form action="{{ route('change-log.store') }}" method="post" class="mt-4 grid gap-3">
                @csrf
                <input type="hidden" name="website_id" value="{{ $selectedWebsite->id }}">
                <input type="datetime-local" name="changed_at" value="{{ now()->format('Y-m-d\\TH:i') }}" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" required>
                <div class="grid gap-3 md:grid-cols-3">
                    <select name="area" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" required>
                        <option value="content">Content</option>
                        <option value="metadata">Metadata</option>
                        <option value="technical">Technical</option>
                        <option value="internal-links">Internal Links</option>
                        <option value="redirects">Redirects</option>
                        <option value="schema">Schema</option>
                        <option value="other">Other</option>
                    </select>
                    <select name="impact_level" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" required>
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                    <input name="title" placeholder="Short change title" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" required>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    <textarea name="old_value" rows="3" placeholder="Before" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm"></textarea>
                    <textarea name="new_value" rows="3" placeholder="After" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm"></textarea>
                </div>
                <textarea name="notes" rows="2" placeholder="Notes / expected impact" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm"></textarea>
                <button class="rounded-md bg-sky-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-sky-400">Save change</button>
            </form>
        </section>

        <section class="mt-6 overflow-x-auto rounded-xl border border-slate-800 bg-slate-900/70">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="px-3 py-3">Date</th>
                        <th class="px-3 py-3">Area</th>
                        <th class="px-3 py-3">Impact</th>
                        <th class="px-3 py-3">Title</th>
                        <th class="px-3 py-3">Notes</th>
                        <th class="px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="border-b border-slate-900/70 align-top">
                            <td class="px-3 py-3">{{ $log->changed_at->format('Y-m-d H:i') }}</td>
                            <td class="px-3 py-3">{{ $log->area }}</td>
                            <td class="px-3 py-3 uppercase">{{ $log->impact_level }}</td>
                            <td class="px-3 py-3">{{ $log->title }}</td>
                            <td class="px-3 py-3 max-w-[24rem]">{{ $log->notes ?: '-' }}</td>
                            <td class="px-3 py-3">
                                <form action="{{ route('change-log.destroy', $log) }}" method="post" onsubmit="return confirm('Delete this log entry?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded border border-red-500/50 px-2 py-1 text-xs text-red-300">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-5 text-slate-500">No changes logged yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        @if ($logs instanceof \Illuminate\Contracts\Pagination\Paginator)
            <div class="mt-6">{{ $logs->links() }}</div>
        @endif
    @endif
</x-layouts.app>
