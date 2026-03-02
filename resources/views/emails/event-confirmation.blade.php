<!-- resources/views/emails/event-confirmation.blade.php -->
@component('mail::message')
# Event Booking Confirmation

Your event has been successfully booked. Here are the details:

**Event Title:** {{ $event->title }}
**Description:** {{ $event->description }}
**Start Date:** {{ $event->start_date }}
**End Date:** {{ $event->end_date }}
**Event Type:** {{ $event->eventType->name }}
**Event Mode:** {{ $event->eventMode->name }}

**Coordinators:** {{ $coordinators->pluck('name')->join(', ') }}
@if($faculties->count())
**Faculties:** {{ $faculties->pluck('name')->join(', ') }}
@endif
**Venues:** {{ $venues->pluck('name')->join(', ') }}
@if($event->custom_amenities_request)
**Additional Amenities Request:** {{ $event->custom_amenities_request }}
@endif

Thanks,
{{ config('app.name') }}
@endcomponent