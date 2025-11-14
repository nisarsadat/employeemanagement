<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class StoreDonelostplaceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'place_name'           => ['nullable','string'],

            'lost_person_name'     => ['nullable','string'],
            'lost_person_f_name'   => ['nullable','string'],
            'lost_person_phone'    => ['nullable','string'],
            'lost_person_job'      => ['nullable','string'],

            'main_province'        => ['nullable','string'],
            'main_district'        => ['nullable','string'],
            'main_village'         => ['nullable','string'],

            'now_province'         => ['nullable','string'],
            'now_district'         => ['nullable','string'],
            'now_village'          => ['nullable','string'],

            'province'             => ['nullable','string'],
            'zone'                 => ['nullable','string'],
            'district'             => ['nullable','string'],
            'village'              => ['nullable','string'],
            'specific_place'       => ['nullable','string'],
            'coordinates'          => ['nullable','string'],
            'Province_addr'                      => ['nullable','string'],

            'lost_quantity'        => ['nullable','integer'],

            'resource_id'          => ['nullable','integer','exists:resources,id'],
            'note'                 => ['nullable','string'],
        ];
    }
}
