@component('mail::message')
# New Tapal Assigned to You

**Tapal Number:** {{ $tapal->tapal_number }}
**Subject:** {{ $tapal->subject }}
**Assigned By:** {{ $sender->name }}
**Date:** {{ now()->format('d-M-Y H:i') }}

**Action Required:** Please review and take necessary action

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
