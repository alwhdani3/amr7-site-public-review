<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicket extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'subject'     => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'priority'    => ['nullable', 'in:low,medium,high'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ];
    }
}
