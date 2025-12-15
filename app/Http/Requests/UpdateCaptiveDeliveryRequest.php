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
            'picture' => ['nullable', 'image'],

            'name' => ['sometimes', 'nullable', 'string'],
            'father_name' => ['sometimes', 'nullable', 'string'],
            'nickname' => ['sometimes', 'nullable', 'string'],
            'grand_father_name' => ['sometimes', 'nullable', 'string'],

            'main_province' => ['sometimes', 'nullable', 'string'],
            'main_district' => ['sometimes', 'nullable', 'string'],
            'main_village' => ['sometimes', 'nullable', 'string'],

            'now_province' => ['sometimes', 'nullable', 'string'],
            'now_district' => ['sometimes', 'nullable', 'string'],
            'now_village' => ['sometimes', 'nullable', 'string'],

            'halat_madani' => ['sometimes', 'nullable', 'string'],
            'age' => ['sometimes', 'nullable', 'string', 'max:100'],
            'tazkira' => ['sometimes', 'nullable', 'string'],
            'criminal_history' => ['sometimes', 'nullable', 'string'],

            'old_job' => ['sometimes', 'nullable', 'string'],
            'new_job' => ['sometimes', 'nullable', 'string'],

            'language' => ['sometimes', 'nullable', 'string'],
            'scientific_level' => ['sometimes', 'nullable', 'string'],

            'captive_place' => ['sometimes', 'nullable', 'string'],
            'captive_marja' => ['sometimes', 'nullable', 'string'],
            'captive_date' => ['sometimes', 'nullable', 'string'],
            'captive_hour' => ['sometimes', 'nullable', 'string'],

            'suspect_type' => ['sometimes', 'nullable', 'string'],
            'phone_number' => ['sometimes', 'nullable', 'string'],
            'captive_halat' => ['sometimes', 'nullable', 'string'],
            'suspect_tools' => ['sometimes', 'nullable', 'string'],
            'Province_addr' => ['sometimes','nullable','string'],
            'captive_reason' => ['sometimes','nullable','string'],
            'taslimi_warkawonky_person' => ['sometimes','nullable','string'],
            'taslimi_akhistonky_person' => ['sometimes','nullable','string'],
            'taslimi_akhistonky_organ' => ['sometimes','nullable','string'],
            'maznonyat_type' => ['sometimes','nullable','string'],
            'coordinates' => ['sometimes','nullable','string'],
            'notes' => ['sometimes', 'nullable', 'string'],

            // optional doc uploads during update
            'documents' => ['sometimes', 'array'],
            'documents.*' => ['file'],
        ];
    }
}
