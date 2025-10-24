<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGovernmentPlaceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'government_place_name' => ['sometimes','nullable','string'],
            'daily_clientele'       => ['sometimes','nullable','integer'],

            'responsible_name'      => ['sometimes','nullable','string'],
            'responsible_f_name'    => ['sometimes','nullable','string'],
            'responsible_possition' => ['sometimes','nullable','string'],
            'responsible_phone'     => ['sometimes','nullable','string'],

            'province'              => ['sometimes','nullable','string'],
            'zone'                  => ['sometimes','nullable','string'],
            'district'              => ['sometimes','nullable','string'],
            'village'               => ['sometimes','nullable','string'],
            'specefic_place'        => ['sometimes','nullable','string'],
            'coordinates'           => ['sometimes','nullable','string'],

            'tafseel'               => ['sometimes','nullable','string'],
            'amnyatee_khala'        => ['sometimes','nullable','string'],
            'guard_quantity'        => ['sometimes','nullable','integer'],

            'resource_id'           => ['sometimes','nullable','integer','exists:resources,id'],
            'note'                  => ['sometimes','nullable','string'],
        ];
    }
}
