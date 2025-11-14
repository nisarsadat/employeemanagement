<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class StoreUniversityRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'uni_name'                   => ['nullable','string'],
            'uni_type'                   => ['nullable','string'],

            'responsible_name'           => ['nullable','string'],
            'responsible_f_name'         => ['nullable','string'],
            'responsible_position'       => ['nullable','string'],
            'responsible_phone'          => ['nullable','string'],

            'male_teachers_quantity'     => ['nullable','integer'],
            'female_teachers_quantity'   => ['nullable','integer'],
            'male_students_quantity'     => ['nullable','integer'],
            'female_students_quantity'   => ['nullable','integer'],

            'province'                   => ['nullable','string'],
            'zone'                       => ['nullable','string'],
            'district'                   => ['nullable','string'],
            'village'                    => ['nullable','string'],
            'specific_place'             => ['nullable','string'],
            'street_number'              => ['nullable','string'],
            'home_number'                => ['nullable','string'],

            'coordinates'                => ['nullable','string'],

            'resource_id'                => ['nullable','integer','exists:resources,id'],
            'note'                       => ['nullable','string'],
            'Province_addr'         => ['nullable','string'],

        ];
    }
}
