<?php

namespace App\Http\Resources\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRequestMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'service_request_id'  => $this->service_request_id,
            'sender_id'           => $this->sender_id,
            'sender_type'         => $this->sender_type,
            'sender_name'         => $this->whenLoaded('sender', fn () => $this->sender?->name),
            'body'                => $this->body,
            'created_at'          => optional($this->created_at)->toIso8601String(),
        ];
    }
}
