<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePumpRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'pump_name'            => ['sometimes','nullable','string'],
            'pump_type'            => ['sometimes','nullable','string'],
            'main_owner'           => ['sometimes','nullable','string'],

            'responsible_name'     => ['sometimes','nullable','string'],
            'responsible_f_name'   => ['sometimes','nullable','string'],
            'responsible_possition'=> ['sometimes','nullable','string'],
            'responsible_phone'    => ['sometimes','nullable','string'],

            'province'             => ['sometimes','nullable','string'],
            'zone'                 => ['sometimes','nullable','string'],
            'district'             => ['sometimes','nullable','string'],
            'village'              => ['sometimes','nullable','string'],
            'specefic_place'       => ['sometimes','nullable','string'],

            'coordinates'          => ['sometimes','nullable','string'],
            'employee_quantity'    => ['sometimes','nullable','integer'],
            'guard_quantity'       => ['sometimes','nullable','integer'],

            'resource_id'          => ['sometimes','nullable','integer','exists:resources,id'],
            'note'                 => ['sometimes','nullable','string'],
        ];
    }
}
