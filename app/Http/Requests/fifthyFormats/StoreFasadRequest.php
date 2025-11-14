<?php

namespace App\Http\Requests\fifthyformats;

use Illuminate\Foundation\Http\FormRequest;

class StoreFasadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // adjust if you use policies
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string',],
            'nick_name'        => ['nullable', 'string', ],
            'father_name'      => ['nullable', 'string', ],

            'main_province'    => ['nullable', 'string', ],
            'main_district'    => ['nullable', 'string', ],
            'main_village'     => ['nullable', 'string', ],

            'now_province'     => ['nullable', 'string', ],
            'now_zone'         => ['nullable', 'string', ],
            'now_district'     => ['nullable', 'string', ],
            'now_village'      => ['nullable', 'string', ],

            'specific_place'   => ['nullable', 'string', ],
            'street_number'    => ['nullable', 'string', ],
            'home_number'      => ['nullable', 'string', ],

            'jabha'            => ['nullable', 'string', ],
            'supplier_name'    => ['nullable', 'string', ],
            'responsibility'   => ['nullable', 'string', ],
            'activity_zone'    => ['nullable', 'string', ],
            'friends_number'   => ['nullable', 'string', ],
            'phone_number'     => ['nullable', 'string', ],
            'bond_distance'    => ['nullable', 'string', ],
            'last_band_reason' => ['nullable', 'string', ],
            'band_place'       => ['nullable', 'string', ],
            'now_job'          => ['nullable', 'string', ],

            'resource_code'    => ['nullable', 'string', ],
            'resource_phone'   => ['nullable', 'string', ],
            'resource_id'      => ['nullable', 'integer', 'exists:resources,id'],
            'Province_addr' => ['nullable','string'],


            // 'picture'        => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'], // enable if/when used
        ];
    }
}
