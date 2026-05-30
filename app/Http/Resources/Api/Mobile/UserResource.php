<?php

namespace App\Http\Resources\Api\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'public_id'         => $this->public_id,
            'name'              => $this->name,
            'email'             => $this->email,
            'mobile'            => $this->mobile,
            'avatar'            => $this->avatar,
            'locale'            => $this->locale,
            'is_active'         => (bool) $this->is_active,
            'email_verified'    => (bool) $this->email_verified_at,
            'active_company_id' => $this->active_company_id,
            'roles'             => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')->all(), []),
            'companies_count'   => $this->whenCounted('companies'),
        ];
    }
}
