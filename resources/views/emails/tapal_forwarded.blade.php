@component('mail::message')
# New Tapal Forwarded to You

**Tapal Number:** {{ $tapal->tapal_number }}
**Subject:** {{ $tapal->subject }}
**From:** {{ $sender->name }}
**Forwarded At:** {{ $movement->created_at->format('d-M-Y H:i') }}

@if($movement->remarks)
**Remarks:**
{{ $movement->remarks }}
@endif

@component('mail::button', ['url' => route('tapals.show', $tapal->id)])
View Tapal Details
@endcomponent

Thanks,
{{ config('app.name') }}
@endcomponent
