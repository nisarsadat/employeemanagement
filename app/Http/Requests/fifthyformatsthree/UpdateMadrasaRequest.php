<?php

namespace App\Http\Requests\fifthyformatsthree;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMadrasaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['sometimes','required','string'],
            'madrasa_type' => ['nullable','string'],

            'responsible_name' => ['nullable','string'],
            'responsible_f_name' => ['nullable','string'],
            'responsible_position' => ['nullable','string'],
            'responsible_phone' => ['nullable','string'],
            'responsible_main_province' => ['nullable','string'],
            'tarafdari_type_responsible' => ['nullable','string'],
            'picture' => ['nullable','image'],

            'mazhab' => ['nullable','string'],
            'male_teacher_quantity' => ['nullable','string'],
            'female_teacher_quantity' => ['nullable','string'],
            'male_students_quantity' => ['nullable','string'],
            'female_students_quantity' => ['nullable','string'],

            'province' => ['nullable','string'],
            'zone' => ['nullable','string'],
            'district' => ['nullable','string'],
            'village' => ['nullable','string'],
            'specific_place' => ['nullable','string'],
            'street_number' => ['nullable','string'],
            'coordinates' => ['nullable','string'],
            'Province_addr' => ['nullable','string'],

            'note' => ['nullable','string'],
            'resource_id' => ['nullable','integer','exists:resources,id'],
        ];
    }
}
