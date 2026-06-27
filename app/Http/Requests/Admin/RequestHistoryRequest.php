<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RequestHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dialog_id' => ['required', 'integer', 'exists:dialogs,id'],
            'messenger_id' => ['required', 'integer', 'exists:messengers,id'],
            'request_text' => ['required', 'string'],
            'response_text' => ['nullable', 'string'],
        ];
    }
}
