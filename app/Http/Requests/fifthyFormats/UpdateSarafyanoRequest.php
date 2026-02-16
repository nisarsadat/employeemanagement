<?php

namespace App\Http\Requests\fifthyFormats;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSarafyanoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes','required','string'],
            'resource_id' => ['nullable','integer','exists:resources,id'],
            'picture' => ['nullable','image'],

            'sarafi_name' => ['nullable','string'],
            'sarafi_type' => ['nullable','string'],
            'father_name' => ['nullable','string'],
            'position' => ['nullable','string'],
            'phone_number' => ['nullable','string'],

            'province' => ['nullable','string'],
            'zone' => ['nullable','string'],
            'district' => ['nullable','string'],
            'village' => ['nullable','string'],
            'specific_place' => ['nullable','string'],
            'market_name' => ['nullable','string'],
            'shop_number' => ['nullable','string'],
            'cordinate' => ['nullable','string'],

            'provinces_hawala' => ['nullable','string'],
            'countries_hawala' => ['nullable','string'],
            'employee_quantity' => ['nullable','string'],
            'Province_addr' => ['nullable','string'],

            'notes' => ['nullable','string'],
        ];
    }
}
