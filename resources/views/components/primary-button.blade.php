<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-md bg-sky-500 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-slate-950 transition hover:bg-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:ring-offset-2 focus:ring-offset-slate-900']) }}>
    {{ $slot }}
</button>
