<?php

namespace App\Http\Requests\fifthyformatstow;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school_name' => ['sometimes','required','string'],
            'school_type' => ['nullable','string'],
            'picture' => ['nullable','image'],

            'principle_name'   => ['nullable','string'],
            'principle_f_name' => ['nullable','string'],
            'principle_phone'  => ['nullable','string'],

            'male_teachers_quantity'   => ['nullable','integer','min:0'],
            'female_teachers_quantity' => ['nullable','integer','min:0'],
            'male_students_quantity'   => ['nullable','integer','min:0'],
            'female_students_quantity' => ['nullable','integer','min:0'],

            'province'       => ['nullable','string'],
            'zone'           => ['nullable','string'],
            'district'       => ['nullable','string'],
            'specific_place' => ['nullable','string'],
            'village'        => ['nullable','string'],
            'street_number'  => ['nullable','string'],
            'coordinates'    => ['nullable','string'],
            'Province_addr' => ['nullable','string'],

            'note'         => ['nullable','string'],
            'resource_id'  => ['nullable','integer','exists:resources,id'],
        ];
    }
}
