<?php

namespace App\Http\Resources\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketReplyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'ticket_id'   => $this->ticket_id,
            'user_id'     => $this->user_id,
            'user_name'   => $this->whenLoaded('user', fn () => $this->user?->name),
            'message'     => $this->message,
            'created_at'  => optional($this->created_at)->toIso8601String(),
        ];
    }
}
