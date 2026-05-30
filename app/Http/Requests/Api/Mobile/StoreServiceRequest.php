<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Mobile API — تخزين طلب خدمة جديد.
 * service_id إلزامي. company_id يُحقَن من middleware mobile.company، ليس من الـ payload.
 */
class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'service_id'         => ['required', 'integer', 'exists:services,id'],
            'phone'              => ['required', 'string', 'max:30'],
            'description'        => ['required', 'string', 'max:5000'],
            'establishment_name' => ['nullable', 'string', 'max:191'],
            'cr_number'          => ['nullable', 'string', 'max:50'],
            'payment_method'     => ['nullable', 'string', 'max:50'],
            'name'               => ['nullable', 'string', 'max:191'],
            'email'              => ['nullable', 'email', 'max:191'],
            'applicant_type'     => ['nullable', 'in:person,company'],
        ];
    }
}
