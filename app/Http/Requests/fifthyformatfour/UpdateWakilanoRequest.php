<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWakilanoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'coordinates'        => ['sometimes','nullable','string'],
            'name'               => ['sometimes','nullable','string'],
            'f_name'             => ['sometimes','nullable','string'],
            'grand_father_name'  => ['sometimes','nullable','string'],

            'main_province'      => ['sometimes','nullable','string'],
            'main_district'      => ['sometimes','nullable','string'],
            'main_village'       => ['sometimes','nullable','string'],

            'now_province'       => ['sometimes','nullable','string'],
            'now_zone'           => ['sometimes','nullable','string'],
            'now_district'       => ['sometimes','nullable','string'],
            'now_village'        => ['sometimes','nullable','string'],
            'now_specific_place' => ['sometimes','nullable','string'],
            'now_street_number'  => ['sometimes','nullable','string'],
            'now_home_number'    => ['sometimes','nullable','string'],

            'Guzar'              => ['sometimes','nullable','string'],
            'place_tafseel'      => ['sometimes','nullable','string'],
            'old_job'            => ['sometimes','nullable','string'],
            'now_job'            => ['sometimes','nullable','string'],

            'home_quantity'      => ['sometimes','nullable','integer'],
            'phone_number'       => ['sometimes','nullable','string'],
            'history_form'       => ['sometimes','nullable','string'],

            'resource_id'        => ['sometimes','nullable','integer','exists:resources,id'],
            'note'               => ['sometimes','nullable','string'],
            'Province_addr'         => ['sometimes','nullable','string'],

        ];
    }
}
