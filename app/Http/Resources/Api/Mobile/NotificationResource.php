<?php

namespace App\Http\Resources\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // resource هنا هو DatabaseNotification (UUID, type, data, read_at, ...)
        $data = $this->data ?? [];
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            $data = is_array($decoded) ? $decoded : [];
        }

        return [
            'id'         => $this->id,
            'type'       => $this->type,
            'data'       => $data,
            'read_at'    => optional($this->read_at)->toIso8601String(),
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
