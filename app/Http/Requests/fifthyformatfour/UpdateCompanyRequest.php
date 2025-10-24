<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'com_name'                           => ['sometimes','nullable','string'],
            'com_type'                           => ['sometimes','nullable','string'],
            'product_type'                       => ['sometimes','nullable','string'],
            'com_level'                          => ['sometimes','nullable','string'],
            'main_owner'                         => ['sometimes','nullable','string'],

            'responsible_name'                   => ['sometimes','nullable','string'],
            'responsible_f_name'                 => ['sometimes','nullable','string'],
            'responsible_position'               => ['sometimes','nullable','string'],
            'responsible_phone'                  => ['sometimes','nullable','string'],

            'com_province'                       => ['sometimes','nullable','string'],
            'com_zone'                           => ['sometimes','nullable','string'],
            'com_district'                       => ['sometimes','nullable','string'],
            'com_village'                        => ['sometimes','nullable','string'],
            'com_specific_place'                 => ['sometimes','nullable','string'],
            'com_coordinates'                    => ['sometimes','nullable','string'],

            'male_employee_quantity_inner'       => ['sometimes','nullable','integer'],
            'female_employee_quantity_inner'     => ['sometimes','nullable','integer'],
            'male_employee_quantity_out'         => ['sometimes','nullable','integer'],
            'female_employee_quantity_out'       => ['sometimes','nullable','integer'],
            'guard_quantity'                     => ['sometimes','nullable','integer'],

            'resource_id'                        => ['sometimes','nullable','integer','exists:resources,id'],
            'note'                               => ['sometimes','nullable','string'],
        ];
    }
}
