<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParkRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'park_name'             => ['sometimes','nullable','string'],
            'park_type'             => ['sometimes','nullable','string'],
            'dayly_clientele'       => ['sometimes','nullable','integer'],

            'responsible_name'      => ['sometimes','nullable','string'],
            'responsible_f_name'    => ['sometimes','nullable','string'],
            'responsible_phone'     => ['sometimes','nullable','string'],
            'responsible_possition' => ['sometimes','nullable','string'],

            'province'              => ['sometimes','nullable','string'],
            'zone'                  => ['sometimes','nullable','string'],
            'village'               => ['sometimes','nullable','string'],
            'specefic_place'        => ['sometimes','nullable','string'],
            'coordinates'           => ['sometimes','nullable','string'],

            'employee_quantity'     => ['sometimes','nullable','integer'],

            'resource_id'           => ['sometimes','nullable','integer','exists:resources,id'],
            'notes'                 => ['sometimes','nullable','string'],
        ];
    }
}
