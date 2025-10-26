<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'            => ['sometimes','string'],
            'price'           => ['sometimes','numeric','min:0'],
            'account_type_id' => ['sometimes','exists:account_types,id'],
            'details'         => ['nullable','string'],
        ];
    }
}


