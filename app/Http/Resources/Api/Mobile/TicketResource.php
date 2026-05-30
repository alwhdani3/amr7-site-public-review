<?php

namespace App\Http\Resources\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'ticket_number'  => $this->ticket_number,
            'company_id'     => $this->company_id,
            'user_id'        => $this->user_id,
            'subject'        => $this->subject,
            'description'    => $this->description,
            'status'         => $this->status,
            'status_label'   => $this->status_label,
            'status_color'   => $this->status_color,
            'priority'       => $this->priority,
            'priority_label' => $this->priority_label,
            'priority_color' => $this->priority_color,
            'sla_deadline'   => optional($this->sla_deadline)->toIso8601String(),
            'last_reply_at'  => optional($this->last_reply_at)->toIso8601String(),
            'is_overdue'     => $this->is_overdue,
            'created_at'     => optional($this->created_at)->toIso8601String(),
        ];
    }
}
