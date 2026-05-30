<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Mobile API Phase 2 — تحديث الملف الشخصي.
 *
 * نتعمَّد عدم السماح بتعديل: email, password, role, is_active, public_id.
 * email يتطلب تحقّقًا منفصلًا — يُضاف في Phase 3.
 */
class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'name'      => ['sometimes', 'required', 'string', 'max:191'],
            'mobile'    => ['sometimes', 'nullable', 'string', 'max:30'],
            'bio'       => ['sometimes', 'nullable', 'string', 'max:1000'],
            'locale'    => ['sometimes', 'nullable', 'in:ar,en'],
            'job_title' => ['sometimes', 'nullable', 'string', 'max:191'],
            'signature' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}
