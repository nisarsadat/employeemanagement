<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountTypeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['sometimes','string', Rule::unique('account_types','name')->ignore($this->route('account_type'))],
            'note' => ['nullable','string'],
        ];
    }
}

