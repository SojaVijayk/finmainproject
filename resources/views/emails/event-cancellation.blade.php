<!-- resources/views/emails/event-cancellation.blade.php -->
@component('mail::message')
# Event Cancelled

The following event has been cancelled:

**Event:** {{ $event->title }}
**Date:** {{ $event->start_date }} to {{ $event->end_date }}
**Cancelled By:** {{ $user->name }}

@component('mail::button', ['url' => url('/calendar')])
View Calendar
@endcomponent

@endcomponent