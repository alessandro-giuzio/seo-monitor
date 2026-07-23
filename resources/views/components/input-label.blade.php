@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-xs font-medium text-slate-400 mb-1']) }}>
    {{ $value ?? $slot }}
</label>
