<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'       => ['required', 'email', 'max:191'],
            'password'    => ['required', 'string', 'min:1', 'max:191'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ];
    }
}
