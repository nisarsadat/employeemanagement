<?php

namespace App\Http\Requests\fifthyFormats;

use Illuminate\Foundation\Http\FormRequest;

class StoreSoldierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string'],
            'resource_id' => ['nullable','integer','exists:resources,id'],

            'father_name' => ['nullable','string'],
            'main_province' => ['nullable','string'],
            'main_district' => ['nullable','string'],
            'main_village' => ['nullable','string'],
            'now_province' => ['nullable','string'],
            'now_zone' => ['nullable','string'],
            'now_district' => ['nullable','string'],
            'now_village' => ['nullable','string'],
            'specific_place' => ['nullable','string'],
            'street_number' => ['nullable','string'],
            'home_number' => ['nullable','string'],
            'old_job' => ['nullable','string'],
            'now_job' => ['nullable','string'],
            'guard_amount' => ['nullable','string'],
            'phone_number' => ['nullable','string'],
            'notes' => ['nullable','string'],
            'Province_addr' => ['nullable','string'],

        ];
    }
}
