<?php

namespace App\Http\Requests\fifthyFormats;

use Illuminate\Foundation\Http\FormRequest;

class StoreMulkanoRequest extends FormRequest
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

            'picture' => ['nullable','image'],
            'father_name' => ['nullable','string'],
            'grand_father_name' => ['nullable','string'],

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
            'guzar' => ['nullable','string'],

            'tafseel_sahi' => ['nullable','string'],
            'street_amount' => ['nullable','string'],
            'old_job' => ['nullable','string'],
            'new_job' => ['nullable','string'],
            'agent_quantity' => ['nullable','string'],
            'home_quantity' => ['nullable','string'],
            'phone_number' => ['nullable','string'],
            'jorm_history' => ['nullable','string'],
            'tanzim' => ['nullable','string'],

            'notes' => ['nullable','string'],
        ];
    }
}
