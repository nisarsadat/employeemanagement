<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDonelostplaceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'place_name'           => ['sometimes','nullable','string'],
            'picture' => ['nullable','image'],

            'lost_person_name'     => ['sometimes','nullable','string'],
            'lost_person_f_name'   => ['sometimes','nullable','string'],
            'lost_person_phone'    => ['sometimes','nullable','string'],
            'lost_person_job'      => ['sometimes','nullable','string'],

            'main_province'        => ['sometimes','nullable','string'],
            'main_district'        => ['sometimes','nullable','string'],
            'main_village'         => ['sometimes','nullable','string'],

            'now_province'         => ['sometimes','nullable','string'],
            'now_district'         => ['sometimes','nullable','string'],
            'now_village'          => ['sometimes','nullable','string'],

            'province'             => ['sometimes','nullable','string'],
            'zone'                 => ['sometimes','nullable','string'],
            'district'             => ['sometimes','nullable','string'],
            'village'              => ['sometimes','nullable','string'],
            'specific_place'       => ['sometimes','nullable','string'],
            'coordinates'          => ['sometimes','nullable','string'],

            'lost_quantity'        => ['sometimes','nullable','integer'],

            'resource_id'          => ['sometimes','nullable','integer','exists:resources,id'],
            'note'                 => ['sometimes','nullable','string'],
            'Province_addr'         => ['sometimes','nullable','string'],

        ];
    }
}
