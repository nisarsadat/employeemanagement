<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class UpdateComplexRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'province'               => ['sometimes','nullable','string'],
            'zone'                   => ['sometimes','nullable','string'],
            'district'               => ['sometimes','nullable','string'],
            'village'                => ['sometimes','nullable','string'],
            'specefic_place'         => ['sometimes','nullable','string'],
            'street_number'          => ['sometimes','nullable','string'],
            'home_number'            => ['sometimes','nullable','string'],

            'complex_name'           => ['sometimes','nullable','string'],
            'activity_type'          => ['sometimes','nullable','string'],
            'activity_place'         => ['sometimes','nullable','string'],
            'supplier'               => ['sometimes','nullable','string'],

            'enter_male_quantity'    => ['sometimes','nullable','integer'],
            'outer_male_quantity'    => ['sometimes','nullable','integer'],
            'enter_femal_quantity'   => ['sometimes','nullable','integer'],
            'outer_femal_quantity'   => ['sometimes','nullable','integer'],

            'responsible_name'       => ['sometimes','nullable','string'],
            'responsible_f_name'     => ['sometimes','nullable','string'],
            'responsible_phone'      => ['sometimes','nullable','string'],
            'responsible_country'    => ['sometimes','nullable','string'],

            'guard_quantity'         => ['sometimes','nullable','integer'],
            'Province_addr'         => ['sometimes','nullable','string'],
            'coordinates'         => ['sometimes','nullable','string'],

            'resource_id'            => ['sometimes','nullable','integer','exists:resources,id'],
            'note'                   => ['sometimes','nullable','string'],
        ];
    }
}
