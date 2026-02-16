<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUniversityRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'uni_name'                   => ['sometimes','nullable','string'],
            'uni_type'                   => ['sometimes','nullable','string'],
            'picture' => ['nullable','image'],

            'responsible_name'           => ['sometimes','nullable','string'],
            'responsible_f_name'         => ['sometimes','nullable','string'],
            'responsible_position'       => ['sometimes','nullable','string'],
            'responsible_phone'          => ['sometimes','nullable','string'],

            'male_teachers_quantity'     => ['sometimes','nullable','integer'],
            'female_teachers_quantity'   => ['sometimes','nullable','integer'],
            'male_students_quantity'     => ['sometimes','nullable','integer'],
            'female_students_quantity'   => ['sometimes','nullable','integer'],

            'province'                   => ['sometimes','nullable','string'],
            'zone'                       => ['sometimes','nullable','string'],
            'district'                   => ['sometimes','nullable','string'],
            'village'                    => ['sometimes','nullable','string'],
            'specific_place'             => ['sometimes','nullable','string'],
            'street_number'              => ['sometimes','nullable','string'],
            'home_number'                => ['sometimes','nullable','string'],

            'coordinates'                => ['sometimes','nullable','string'],

            'resource_id'                => ['sometimes','nullable','integer','exists:resources,id'],
            'note'                       => ['sometimes','nullable','string'],
            'Province_addr'         => ['sometimes','nullable','string'],

        ];
    }
}
