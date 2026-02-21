<x-layouts.app :title="'Audits - SEO Toolkit'">
    <h1 class="text-2xl font-semibold">SEO Audits</h1>
    <p class="mt-1 text-sm text-slate-400">On-page snapshots with actionable issue lists.</p>

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
