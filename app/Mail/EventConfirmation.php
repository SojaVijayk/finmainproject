<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Event;

class EventConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function build()
    {
        return $this->subject('Event Booking Confirmation')
    ->markdown('emails.event-confirmation', [
        'event' => $this->event,
        'coordinators' => $this->event->coordinators,
        'faculties' => $this->event->faculties,
        'venues' => $this->event->venues,
    ]);
    }
}
