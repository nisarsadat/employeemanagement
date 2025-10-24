<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class StoreParkRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'park_name'             => ['nullable','string'],
            'park_type'             => ['nullable','string'],
            'dayly_clientele'       => ['nullable','integer'],

            'responsible_name'      => ['nullable','string'],
            'responsible_f_name'    => ['nullable','string'],
            'responsible_phone'     => ['nullable','string'],
            'responsible_possition' => ['nullable','string'],

            'province'              => ['nullable','string'],
            'zone'                  => ['nullable','string'],
            'village'               => ['nullable','string'],
            'specefic_place'        => ['nullable','string'],
            'coordinates'           => ['nullable','string'],

            'employee_quantity'     => ['nullable','integer'],

            'resource_id'           => ['nullable','integer','exists:resources,id'],
            'notes'                 => ['nullable','string'],
        ];
    }
}
