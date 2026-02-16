<?php

namespace App\Http\Requests\fifthyFormats;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMahajeratRequest extends FormRequest
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

            'father_name' => ['nullable','string'],
            'scientific_level' => ['nullable','string'],

            'main_province' => ['nullable','string'],
            'main_district' => ['nullable','string'],
            'main_village' => ['nullable','string'],

            'now_province' => ['nullable','string'],
            'now_zone' => ['nullable','string'],
            'now_district' => ['nullable','string'],
            'now_vinage' => ['nullable','string'],
            'specific_place' => ['nullable','string'],
            'street_number' => ['nullable','string'],
            'home_number' => ['nullable','string'],
            'cordinates' => ['nullable','string'],

            'country' => ['nullable','string'],
            'male_amount' => ['nullable','string'],
            'female_amount' => ['nullable','string'],
            'phone_number' => ['nullable','string'],
            'Province_addr' => ['nullable','string'],
            'note' => ['nullable','string'],
        ];
    }
}
