<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class StorePumpRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'pump_name'            => ['nullable','string'],
            'pump_type'            => ['nullable','string'],
            'main_owner'           => ['nullable','string'],

            'responsible_name'     => ['nullable','string'],
            'responsible_f_name'   => ['nullable','string'],
            'responsible_possition'=> ['nullable','string'],
            'responsible_phone'    => ['nullable','string'],

            'province'             => ['nullable','string'],
            'zone'                 => ['nullable','string'],
            'district'             => ['nullable','string'],
            'village'              => ['nullable','string'],
            'specefic_place'       => ['nullable','string'],

            'coordinates'          => ['nullable','string'],
            'employee_quantity'    => ['nullable','integer'],
            'guard_quantity'       => ['nullable','integer'],
            'Province_addr'         => ['nullable','string'],

            'resource_id'          => ['nullable','integer','exists:resources,id'],
            'note'                 => ['nullable','string'],
        ];
    }
}
