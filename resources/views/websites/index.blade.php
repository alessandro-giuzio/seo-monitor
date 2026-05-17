<x-layouts.app :title="'Websites - SEO Toolkit'">
    <div x-data="{ open: {{ $errors->any() ? 'true' : 'false' }}, more: false }">

        {{-- Page heading --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Websites</h1>
                <p class="mt-1 text-sm text-slate-400">All tracked properties for SEO monitoring.</p>
            </div>
            <button @click="open = !open"
                    class="rounded-lg border border-slate-700 px-4 py-2 text-sm hover:border-sky-400 hover:text-sky-300">
                <span x-text="open ? '✕ Cancel' : '+ Add website'"></span>
            </button>
        </div>

        {{-- Add website form --}}
        <div x-show="open" x-transition class="mt-4 rounded-xl border border-slate-700 bg-slate-900/70 p-6">
            <form method="POST" action="{{ route('websites.store') }}">
                @csrf

                {{-- Required fields --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Name <span class="text-red-400">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               placeholder="My Website"
                               class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                        @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Base URL <span class="text-red-400">*</span></label>
                        <input type="url" name="base_url" value="{{ old('base_url') }}" required
                               placeholder="https://example.com"
                               class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                        @error('base_url') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- More options toggle --}}
                <button type="button" @click="more = !more"
                        class="mt-4 text-xs text-slate-500 hover:text-slate-300">
                    <span x-text="more ? '▼ Less options' : '▶ More options'"></span>
                </button>

                <div x-show="more" x-transition class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">GSC Property</label>
                        <input type="text" name="gsc_property" value="{{ old('gsc_property') }}"
                               placeholder="sc-domain:example.com"
                               class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Industry</label>
                        <input type="text" name="industry" value="{{ old('industry') }}"
                               placeholder="E-commerce"
                               class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Target Country <span class="text-slate-600">(2-letter code)</span></label>
                        <input type="text" name="target_country" value="{{ old('target_country') }}"
                               placeholder="US" maxlength="2"
                               class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                        @error('target_country') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Alert Email</label>
                        <input type="email" name="alert_email" value="{{ old('alert_email') }}"
                               placeholder="you@example.com"
                               class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                        @error('alert_email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Crawl Frequency (hours)</label>
                        <input type="number" name="crawl_frequency_hours" value="{{ old('crawl_frequency_hours', 24) }}"
                               min="1" max="168"
                               class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Notes</label>
                        <textarea name="notes" rows="2" maxlength="4000"
                                  class="w-full rounded-md border border-slate-700 bg-slate-900 px-3 py-2 text-sm focus:border-sky-500 focus:outline-none">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" @click="open = false"
                            class="rounded-lg border border-slate-700 px-4 py-2 text-sm hover:border-slate-500">
                        Cancel
                    </button>
                    <button type="submit"
                            class="rounded-lg bg-sky-600 px-4 py-2 text-sm font-medium text-white hover:bg-sky-500">
                        Add Website
                    </button>
                </div>
            </form>
        </div>

        {{-- Website grid --}}
        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($websites as $website)
                <a href="{{ route('websites.show', $website) }}" class="rounded-xl border border-slate-800 bg-slate-900/70 p-4 hover:border-sky-400/60">
                    <h2 class="text-lg font-medium">{{ $website->name }}</h2>
                    <p class="mt-1 text-sm text-slate-400">{{ $website->base_url }}</p>
                    <p class="mt-3 text-xs text-slate-500">Updated {{ $website->updated_at->diffForHumans() }}</p>
                </a>
            @endforeach
        </div>

        <div class="mt-6">{{ $websites->links() }}</div>

    </div>
</x-layouts.app>
