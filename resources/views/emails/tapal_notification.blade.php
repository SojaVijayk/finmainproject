@component('mail::message')
# Tapal Notification

**Tapal Number:** {{ $tapal->tapal_number }}
**Subject:** {{ $tapal->subject }}
**Sent By:** {{ $sender->name }}
**Date:** {{ now()->format('d-M-Y H:i') }}

**Note:** This is for your information.

@component('mail::button', ['url' => route('tapals.show', $tapal->id)])
View Tapal Details
@endcomponent

Thanks,
{{ config('app.name') }}
@endcomponent