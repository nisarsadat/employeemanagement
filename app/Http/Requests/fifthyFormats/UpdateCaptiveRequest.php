<?php

namespace App\Http\Requests\fifthyFormats;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCaptiveRequest extends FormRequest
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
            'nick_name' => ['nullable','string'],
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
            'specific_form' => ['nullable','string'],
            'band_distince' => ['nullable','string'],
            'now_activity' => ['nullable','string'],
            'phone_number' => ['nullable','string'],
            'Province_addr' => ['nullable','string'],
            'note' => ['nullable','string'],
        ];
    }
}
