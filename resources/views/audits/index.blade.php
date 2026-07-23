<x-layouts.app :title="'Audits - SEO Toolkit'">
    <div x-data="{ open: {{ $errors->any() ? 'true' : 'false' }}, more: false }">

        {{-- Page heading --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">SEO Audits</h1>
                <p class="mt-1 text-sm text-slate-400">On-page snapshots with actionable issue lists.</p>
            </div>
            <button @click="open = !open"
                    class="rounded-lg border border-slate-700 px-4 py-2 text-sm hover:border-sky-400 hover:text-sky-300">
                <span x-text="open ? '✕ Cancel' : '+ Run audit'"></span>
            </button>
        </div>

        {{-- Run audit form --}}
        <div x-show="open" x-transition class="mt-4 rounded-xl border border-slate-700 bg-slate-900/70 p-6">
            <form method="POST" action="{{ route('audits.store') }}">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">URL <span class="text-red-400">*</span></label>
                        <input type="url" name="url" value="{{ old('url') }}" required
                               placeholder="https://example.com/page"
                               class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                        @error('url') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Website</label>
                        <select name="website_id"
                                class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                            <option value="">— Unlinked —</option>
                            @foreach ($websites as $website)
                                <option value="{{ $website->id }}" @selected(old('website_id') == $website->id)>{{ $website->name }}</option>
                            @endforeach
                        </select>
                        @error('website_id') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- More options toggle --}}
                <button type="button" @click="more = !more"
                        class="mt-4 text-xs text-slate-500 hover:text-slate-300">
                    <span x-text="more ? '▼ Less options' : '▶ More options'"></span>
                </button>

                <div x-show="more" x-transition class="mt-3 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Audited At</label>
                        <input type="datetime-local" name="audited_at" value="{{ old('audited_at') }}"
                               class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                        @error('audited_at') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Raw HTML <span class="text-slate-600">(optional — leave blank to fetch the URL)</span></label>
                        <textarea name="raw_html" rows="1"
                                  placeholder="Paste HTML to analyze offline instead of fetching the URL"
                                  class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">{{ old('raw_html') }}</textarea>
                        @error('raw_html') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit"
                            class="rounded-md bg-sky-500 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-950 hover:bg-sky-400">
                        Run Audit
                    </button>
                </div>
            </form>
        </div>

    </div>

    <div class="mt-6 overflow-x-auto rounded-xl border border-slate-800 bg-slate-900/70">
        <table class="min-w-full text-sm">
            <thead>
            <tr class="border-b border-slate-800 text-left text-slate-400">
                <th class="px-3 py-3">Date</th>
                <th class="px-3 py-3">Website</th>
                <th class="px-3 py-3">URL</th>
                <th class="px-3 py-3">Status</th>
                <th class="px-3 py-3">Score</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($audits as $audit)
                <tr class="border-b border-slate-900/70">
                    <td class="px-3 py-3">{{ $audit->audited_at->format('Y-m-d H:i') }}</td>
                    <td class="px-3 py-3">{{ $audit->website?->name ?? 'Unlinked' }}</td>
                    <td class="px-3 py-3">
                        <a href="{{ route('audits.show', $audit) }}" class="text-sky-300 hover:text-sky-200">{{ $audit->url }}</a>
                    </td>
                    <td class="px-3 py-3 capitalize">{{ $audit->status }}</td>
                    <td class="px-3 py-3">{{ $audit->score }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-3 py-5 text-slate-500">No audits yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $audits->links() }}</div>
</x-layouts.app>
