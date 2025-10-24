<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'com_name'                           => ['nullable','string'],
            'com_type'                           => ['nullable','string'],
            'product_type'                       => ['nullable','string'],
            'com_level'                          => ['nullable','string'],
            'main_owner'                         => ['nullable','string'],

            'responsible_name'                   => ['nullable','string'],
            'responsible_f_name'                 => ['nullable','string'],
            'responsible_position'               => ['nullable','string'],
            'responsible_phone'                  => ['nullable','string'],

            'com_province'                       => ['nullable','string'],
            'com_zone'                           => ['nullable','string'],
            'com_district'                       => ['nullable','string'],
            'com_village'                        => ['nullable','string'],
            'com_specific_place'                 => ['nullable','string'],
            'com_coordinates'                    => ['nullable','string'],

            'male_employee_quantity_inner'       => ['nullable','integer'],
            'female_employee_quantity_inner'     => ['nullable','integer'],
            'male_employee_quantity_out'         => ['nullable','integer'],
            'female_employee_quantity_out'       => ['nullable','integer'],
            'guard_quantity'                     => ['nullable','integer'],

            'resource_id'                        => ['nullable','integer','exists:resources,id'],
            'note'                               => ['nullable','string'],
        ];
    }
}
