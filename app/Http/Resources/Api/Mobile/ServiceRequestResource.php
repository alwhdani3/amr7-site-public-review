<?php

namespace App\Http\Resources\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'service_id'         => $this->service_id,
            'company_id'         => $this->company_id,
            'user_id'            => $this->user_id,
            'establishment_name' => $this->establishment_name,
            'cr_number'          => $this->cr_number,
            'phone'              => $this->phone,
            'description'        => $this->description,
            'status'             => $this->status,
            'status_label'       => $this->status_label,
            'status_color'       => $this->status_color,
            'payment_method'     => $this->payment_method,
            'created_at'         => optional($this->created_at)->toIso8601String(),
            'service'            => $this->whenLoaded('service', fn () => [
                'id'   => $this->service?->id,
                'name' => $this->service?->name ?? null,
            ]),
            'messages' => ServiceRequestMessageResource::collection($this->whenLoaded('messages')),
        ];
    }
}
