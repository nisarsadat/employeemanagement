<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class StoreWakilanoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'coordinates'        => ['nullable','string'],
            'name'               => ['nullable','string'],
            'f_name'             => ['nullable','string'],
            'grand_father_name'  => ['nullable','string'],
            'picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],        

            'main_province'      => ['nullable','string'],
            'main_district'      => ['nullable','string'],
            'main_village'       => ['nullable','string'],

            'now_province'       => ['nullable','string'],
            'now_zone'           => ['nullable','string'],
            'now_district'       => ['nullable','string'],
            'now_village'        => ['nullable','string'],
            'now_specific_place' => ['nullable','string'],
            'now_street_number'  => ['nullable','string'],
            'now_home_number'    => ['nullable','string'],

            'hawza'              => ['nullable','string'],
            'nahya'              => ['nullable','string'],
            'Guzar'              => ['nullable','string'],
            'place_tafseel'      => ['nullable','string'],
            'old_job'            => ['nullable','string'],
            'now_job'            => ['nullable','string'],

            'home_quantity'      => ['nullable','integer'],
            'phone_number'       => ['nullable','string'],
            'history_form'       => ['nullable','string'],
            'Province_addr'         => ['nullable','string'],

            'resource_id'        => ['nullable','integer','exists:resources,id'],
            'note'               => ['nullable','string'],
        ];
    }
}
