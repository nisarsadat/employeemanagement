<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class StoreMasjidRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'masjid_name'                   => ['nullable','string'],
            'masjid_type'                   => ['nullable','string'],

            'responsible_name'              => ['nullable','string'],
            'responsible_f_name'            => ['nullable','string'],
            'responsible_position'          => ['nullable','string'],
            'responsible_mazhab'            => ['nullable','string'],
            'responsible_old_job'           => ['nullable','string'],
            'responsible_now_job'           => ['nullable','string'],
            'responsible_tarfdary'          => ['nullable','string'],

            'responsible_main_province'     => ['nullable','string'],
            'responsible_main_district'     => ['nullable','string'],
            'responsible_main_village'      => ['nullable','string'],
            'responsible_now_province'      => ['nullable','string'],
            'responsible_now_district'      => ['nullable','string'],
            'responsible_now_village'       => ['nullable','string'],

            'masjid_people_mazhab'          => ['nullable','string'],
            'masjid_homes_quantity'         => ['nullable','integer'],
            'masjid_province'               => ['nullable','string'],
            'masjid_zone'                   => ['nullable','string'],
            'masjid_district'               => ['nullable','string'],
            'masjid_village'                => ['nullable','string'],
            'masjid_specific_place'         => ['nullable','string'],
            'masjid_street_number'          => ['nullable','string'],
            'masjid_coordinates'            => ['nullable','string'],

            'resource_id'                   => ['nullable','integer','exists:resources,id'],
            'note'                          => ['nullable','string'],
        ];
    }
}
