<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class TicketReplied extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket
    ) {}

    public function via(object $notifiable): array
    {
        return ['database']; // جاهز للتوسعة (mail / broadcast)
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'title'     => 'تم الرد على تذكرتك',
            'message'   => "تم الرد على التذكرة: {$this->ticket->title}",
            'url'       => route('filament.amr7.resources.tickets.edit', $this->ticket),
        ];
    }
}
