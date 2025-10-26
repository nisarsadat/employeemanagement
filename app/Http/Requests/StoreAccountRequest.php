<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'            => ['required','string'],
            'price'           => ['required','numeric','min:0'],
            'account_type_id' => ['required','exists:account_types,id'],
            'details'         => ['nullable','string'],
        ];
    }
}

