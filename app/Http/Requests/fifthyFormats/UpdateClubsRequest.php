<?php

namespace App\Http\Requests\fifthyFormats;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClubsRequest extends FormRequest
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

            'club_name' => ['nullable','string'],
            'club_type' => ['nullable','string'],
            'father_name' => ['nullable','string'],
            'position' => ['nullable','string'],
            'phone_number' => ['nullable','string'],

            'province' => ['nullable','string'],
            'zone' => ['nullable','string'],
            'district' => ['nullable','string'],
            'specific_place' => ['nullable','string'],
            'cordinate' => ['nullable','string'],
            'am_students' => ['nullable','string'],
            'pm_students' => ['nullable','string'],
            'Province_addr' => ['nullable','string'],

        ];
    }
}
