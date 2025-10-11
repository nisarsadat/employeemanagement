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
            'name' => ['sometimes','required','string','max:255'],
            'resource_id' => ['nullable','integer','exists:resources,id'],

            'club_name' => ['nullable','string','max:255'],
            'club_type' => ['nullable','string','max:255'],
            'father_name' => ['nullable','string','max:255'],
            'position' => ['nullable','string','max:255'],
            'phone_number' => ['nullable','string','max:255'],

            'province' => ['nullable','string','max:255'],
            'zone' => ['nullable','string','max:255'],
            'district' => ['nullable','string','max:255'],
            'specific_place' => ['nullable','string','max:255'],
            'cordinate' => ['nullable','string','max:255'],
            'am_students' => ['nullable','string','max:255'],
            'pm_students' => ['nullable','string','max:255'],
        ];
    }
}
