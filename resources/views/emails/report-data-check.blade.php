<x-mail::message>
# Report data check — {{ $month }}

The client portal drafts monthly reports on the 1st from this data. These websites are missing something:

@foreach ($problems as $problem)
**{{ $problem['website']->name }}**

@foreach ($problem['issues'] as $issue)
- {{ $issue }}
@endforeach

@endforeach
<x-mail::button :url="route('websites.index')">
Open websites
</x-mail::button>
</x-mail::message>
