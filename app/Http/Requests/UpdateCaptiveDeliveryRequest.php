<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCaptiveDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // all optional on update
            'picture' => ['nullable', 'image', 'max:5120'],

            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'father_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'nickname' => ['sometimes', 'nullable', 'string', 'max:255'],
            'grand_father_name' => ['sometimes', 'nullable', 'string', 'max:255'],

            'main_province' => ['sometimes', 'nullable', 'string', 'max:255'],
            'main_district' => ['sometimes', 'nullable', 'string', 'max:255'],
            'main_village' => ['sometimes', 'nullable', 'string', 'max:255'],

            'now_province' => ['sometimes', 'nullable', 'string', 'max:255'],
            'now_district' => ['sometimes', 'nullable', 'string', 'max:255'],
            'now_village' => ['sometimes', 'nullable', 'string', 'max:255'],

            'halat_madani' => ['sometimes', 'nullable', 'string', 'max:255'],
            'age' => ['sometimes', 'nullable', 'string', 'max:50'],
            'tazkira' => ['sometimes', 'nullable', 'string', 'max:255'],
            'criminal_history' => ['sometimes', 'nullable', 'string'],

            'old_job' => ['sometimes', 'nullable', 'string', 'max:255'],
            'new_job' => ['sometimes', 'nullable', 'string', 'max:255'],

            'language' => ['sometimes', 'nullable', 'string', 'max:255'],
            'scientific_level' => ['sometimes', 'nullable', 'string', 'max:255'],

            'captive_place' => ['sometimes', 'nullable', 'string', 'max:255'],
            'captive_marja' => ['sometimes', 'nullable', 'string', 'max:255'],
            'captive_date' => ['sometimes', 'nullable', 'string', 'max:255'],
            'captive_hour' => ['sometimes', 'nullable', 'string', 'max:255'],

            'suspect_type' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'captive_halat' => ['sometimes', 'nullable', 'string', 'max:255'],
            'suspect_tools' => ['sometimes', 'nullable', 'string'],

            'notes' => ['sometimes', 'nullable', 'string'],

            // optional doc uploads during update
            'documents' => ['sometimes', 'array'],
            'documents.*' => ['file'],
        ];
    }
}
