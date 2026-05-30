<?php

namespace App\Http\Requests\Api\Mobile;

use Illuminate\Foundation\Http\FormRequest;

class StoreTicketReply extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:10000'],
        ];
    }
}
