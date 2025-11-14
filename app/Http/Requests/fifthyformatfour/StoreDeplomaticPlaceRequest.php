<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeplomaticPlaceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'deplomatic_place_name' => ['nullable','string'],
            'activity_type'         => ['nullable','string'],
            'activity_place'        => ['nullable','string'],

            'enter_male_quantity'   => ['nullable','integer'],
            'outer_male_quantity'   => ['nullable','integer'],
            'enter_female_quantity' => ['nullable','integer'],
            'outer_female_quantity' => ['nullable','integer'],

            'responsible_name'      => ['nullable','string'],
            'responsible_f_name'    => ['nullable','string'],
            'responsible_possition' => ['nullable','string'],
            'responsible_country'   => ['nullable','string'],
            'responsible_phone'     => ['nullable','string'],

            'province'              => ['nullable','string'],
            'zone'                  => ['nullable','string'],
            'district'              => ['nullable','string'],
            'village'               => ['nullable','string'],
            'specefic_place'        => ['nullable','string'],
            'street_number'         => ['nullable','string'],
            'home_number'           => ['nullable','string'],

            'guard_quantity'        => ['nullable','integer'],
            'coordinates'           => ['nullable','string'],
            'Province_addr'                      => ['nullable','string'],


            'resource_id'           => ['nullable','integer','exists:resources,id'],
            'note'                  => ['nullable','string'],
        ];
    }
}
