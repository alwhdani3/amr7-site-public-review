<?php

namespace App\Http\Resources\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // pivot قد لا يكون محمَّلًا (مثلاً عند عرض شركة منفردة) — نتحقق دفاعيًا.
        $pivot = $this->resource->pivot ?? null;

        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'commercial_name' => $this->commercial_name,
            'cr_number'       => $this->cr_number,
            'city'            => $this->city,
            'status'          => $this->status,
            'cr_expiry_date'  => optional($this->cr_expiry_date)->toDateString(),
            'is_cr_expired'   => $this->isCrExpired(),
            'pivot' => $pivot ? [
                'role'      => $pivot->role,
                'is_active' => (bool) $pivot->is_active,
            ] : null,
        ];
    }
}
