<?php

namespace App\Events;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketReplied implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Ticket $ticket,
        public TicketReply $reply,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('tickets.' . $this->ticket->id);
    }

    public function broadcastAs(): string
    {
        return 'ticket.replied';
    }

    public function broadcastWith(): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'reply_id'  => $this->reply->id,
            'message'   => $this->reply->message,
            'user'      => $this->reply->user->name,
            'created'   => $this->reply->created_at->toDateTimeString(),
        ];
    }
}
