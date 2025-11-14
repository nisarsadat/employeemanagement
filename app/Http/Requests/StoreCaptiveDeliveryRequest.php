<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCaptiveDeliveryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'picture' => ['nullable','image','max:5120'], // 5MB

            'name' => ['nullable','string','max:255'],
            'father_name' => ['nullable','string','max:255'],
            'nickname' => ['nullable','string','max:255'],
            'grand_father_name' => ['nullable','string','max:255'],

            'main_province' => ['nullable','string','max:255'],
            'main_district' => ['nullable','string','max:255'],
            'main_village' => ['nullable','string','max:255'],

            'now_province' => ['nullable','string','max:255'],
            'now_district' => ['nullable','string','max:255'],
            'now_village' => ['nullable','string','max:255'],

            'halat_madani' => ['nullable','string','max:255'],
            'age' => ['nullable','string','max:50'],
            'tazkira' => ['nullable','string','max:255'],
            'criminal_history' => ['nullable','string'],

            'old_job' => ['nullable','string','max:255'],
            'new_job' => ['nullable','string','max:255'],

            'language' => ['nullable','string','max:255'],
            'scientific_level' => ['nullable','string','max:255'],

            'captive_place' => ['nullable','string','max:255'],
            'captive_marja' => ['nullable','string','max:255'],
            'captive_date' => ['nullable','string','max:255'],
            'captive_hour' => ['nullable','string','max:255'],

            'suspect_type' => ['nullable','string','max:255'],
            'phone_number' => ['nullable','string','max:255'],
            'captive_halat' => ['nullable','string','max:255'],
            'suspect_tools' => ['nullable','string'],

            'notes' => ['nullable','string'],
            'Province_addr' => ['nullable','string'],

            // multiple docs on create
            'documents'   => ['sometimes','array'],
            'documents.*' => ['file'], // 20MB each
        ];
    }
}
