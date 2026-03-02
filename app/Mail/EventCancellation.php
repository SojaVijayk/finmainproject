<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Event;

class EventCancellation extends Mailable
{
    use Queueable, SerializesModels;

    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function build()
    {
       return $this->subject('Event Cancellation: ' . $this->event->title)
                ->markdown('emails.event-cancellation', [
                    'event' => $this->event,
                    'user' => auth()->user()
                ]);
    }
}
