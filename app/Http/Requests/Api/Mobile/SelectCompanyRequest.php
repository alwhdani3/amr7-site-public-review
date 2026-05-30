<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;

class SelectCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
        ];
    }
}
