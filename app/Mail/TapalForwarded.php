<?php

namespace App\Mail;

use App\Models\Tapal;
use App\Models\TapalMovement;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TapalForwarded extends Mailable
{
    use Queueable, SerializesModels;

    public $tapal;
    public $movement;
    public $sender;

    public function __construct(Tapal $tapal, TapalMovement $movement, User $sender)
    {
        $this->tapal = $tapal;
        $this->movement = $movement;
        $this->sender = $sender;
    }

    public function build()
    {
        return $this->subject('New Tapal Forwarded to You - ' . $this->tapal->tapal_number)
                    ->markdown('emails.tapal_forwarded');
    }
}
