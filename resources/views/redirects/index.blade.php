<x-layouts.app :title="'Redirect Manager - SEO Toolkit'">
    <h1 class="text-2xl font-semibold">Redirect Manager</h1>
    <p class="mt-1 text-sm text-slate-400">Plan redirects and validate behavior before release.</p>

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
            <h2 class="text-lg font-semibold">Add / Update Redirect Rule</h2>
            <form action="{{ route('redirects.store') }}" method="post" class="mt-4 grid gap-3">
                @csrf
                <input type="hidden" name="website_id" value="{{ $selectedWebsite->id }}">
                <div class="grid gap-3 md:grid-cols-3">
                    <input name="from_path" placeholder="/old-page" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" required>
                    <input name="to_url" placeholder="https://example.com/new-page" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm" required>
                    <select name="status_code" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                        <option value="301">301</option>
                        <option value="302">302</option>
                        <option value="307">307</option>
                        <option value="308">308</option>
                    </select>
                </div>
                <div class="grid gap-3 md:grid-cols-2">
                    <select name="is_active" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                    <input name="notes" placeholder="Notes" class="rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm">
                </div>
                <button class="rounded-md bg-sky-500 px-4 py-2 text-sm font-medium text-slate-950 hover:bg-sky-400">Save rule</button>
            </form>
        </section>

        <section class="mt-6 overflow-x-auto rounded-xl border border-slate-800 bg-slate-900/70">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-left text-slate-400">
                        <th class="px-3 py-3">From</th>
                        <th class="px-3 py-3">To</th>
                        <th class="px-3 py-3">Code</th>
                        <th class="px-3 py-3">Active</th>
                        <th class="px-3 py-3">Last Check</th>
                        <th class="px-3 py-3">Result</th>
                        <th class="px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rules as $rule)
                        <tr class="border-b border-slate-900/70">
                            <td class="px-3 py-3">{{ $rule->from_path }}</td>
                            <td class="px-3 py-3 max-w-[24rem] truncate">{{ $rule->to_url }}</td>
                            <td class="px-3 py-3">{{ $rule->status_code }}</td>
                            <td class="px-3 py-3">{{ $rule->is_active ? 'yes' : 'no' }}</td>
                            <td class="px-3 py-3">{{ $rule->last_checked_at ? $rule->last_checked_at->diffForHumans() : '-' }}</td>
                            <td class="px-3 py-3">{{ $rule->last_check_result ?? '-' }}</td>
                            <td class="px-3 py-3">
                                <div class="flex gap-2">
                                    <form action="{{ route('redirects.check', $rule) }}" method="post">
                                        @csrf
                                        <button class="rounded border border-sky-500/50 px-2 py-1 text-xs text-sky-300">Check</button>
                                    </form>
                                    <form action="{{ route('redirects.destroy', $rule) }}" method="post" onsubmit="return confirm('Delete redirect rule?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded border border-red-500/50 px-2 py-1 text-xs text-red-300">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-3 py-5 text-slate-500">No redirect rules yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        @if ($rules instanceof \Illuminate\Contracts\Pagination\Paginator)
            <div class="mt-6">{{ $rules->links() }}</div>
        @endif
    @endif
</x-layouts.app>
