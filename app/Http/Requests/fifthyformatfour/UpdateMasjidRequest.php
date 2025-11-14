<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMasjidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'coordinates'                   => ['sometimes','nullable','string'],
            'masjid_name'                   => ['sometimes','nullable','string'],
            'masjid_type'                   => ['sometimes','nullable','string'],

            'responsible_name'              => ['sometimes','nullable','string'],
            'responsible_f_name'            => ['sometimes','nullable','string'],
            'responsible_position'          => ['sometimes','nullable','string'],
            'responsible_mazhab'            => ['sometimes','nullable','string'],
            'responsible_old_job'           => ['sometimes','nullable','string'],
            'responsible_now_job'           => ['sometimes','nullable','string'],
            'responsible_tasfelay'          => ['sometimes','nullable','string'],

            'responsible_main_province'     => ['sometimes','nullable','string'],
            'responsible_main_district'     => ['sometimes','nullable','string'],
            'responsible_main_village'      => ['sometimes','nullable','string'],

            'responsible_now_province'      => ['sometimes','nullable','string'],
            'responsible_now_district'      => ['sometimes','nullable','string'],
            'responsible_now_village'       => ['sometimes','nullable','string'],

            'masjid_people_mazhab'          => ['sometimes','nullable','string'],
            'masjid_homes_quantity'         => ['sometimes','nullable','integer'],

            'masjid_province'               => ['sometimes','nullable','string'],
            'masjid_zone'                   => ['sometimes','nullable','string'],
            'masjid_district'               => ['sometimes','nullable','string'],
            'masjid_village'                => ['sometimes','nullable','string'],
            'masjid_specific_place'         => ['sometimes','nullable','string'],
            'masjid_street_number'          => ['sometimes','nullable','string'],
            'masjid_coordinates'            => ['sometimes','nullable','string'],

            'resource_id'                   => ['sometimes','nullable','integer','exists:resources,id'],
            'note'                          => ['sometimes','nullable','string'],
            'Province_addr'         => ['sometimes','nullable','string'],

        ];
    }
}
