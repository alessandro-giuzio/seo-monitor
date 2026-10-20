<x-mail::message>
# New alerts for {{ $website->name }}

{{ $website->base_url }}

@foreach ($alerts as $alert)
**{{ ucfirst($alert->severity) }} — {{ $alert->title }}**
{{ $alert->message }}

@endforeach
<x-mail::button :url="route('alerts.index', ['website_id' => $website->id])">
Open alerts
</x-mail::button>
</x-mail::message>
