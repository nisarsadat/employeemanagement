<?php

namespace App\Http\Requests\fifthyFormats;

use Illuminate\Foundation\Http\FormRequest;

class StoreMahajeratRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'resource_id' => ['nullable','integer','exists:resources,id'],

            'father_name' => ['nullable','string','max:255'],
            'scientific_level' => ['nullable','string','max:255'],

            'main_province' => ['nullable','string','max:255'],
            'main_district' => ['nullable','string','max:255'],
            'main_village' => ['nullable','string','max:255'],

            'now_province' => ['nullable','string','max:255'],
            'now_zone' => ['nullable','string','max:255'],
            'now_district' => ['nullable','string','max:255'],
            'now_vinage' => ['nullable','string','max:255'],
            'specific_place' => ['nullable','string','max:255'],
            'street_number' => ['nullable','string','max:255'],
            'home_number' => ['nullable','string','max:255'],
            'cordinates' => ['nullable','string','max:255'],

            'country' => ['nullable','string','max:255'],
            'male_amount' => ['nullable','string','max:255'],
            'female_amount' => ['nullable','string','max:255'],
            'phone_number' => ['nullable','string','max:255'],

            'note' => ['nullable','string'],
        ];
    }
}
