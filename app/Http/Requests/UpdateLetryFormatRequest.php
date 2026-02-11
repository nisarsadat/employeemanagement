<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLetryFormatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $letryFormat = $this->route('letry_format'); // model from route binding

        return [
            'letry_format_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('letry_formats', 'letry_format_name')->ignore($letryFormat?->id),
            ],
        ];
    }
}
