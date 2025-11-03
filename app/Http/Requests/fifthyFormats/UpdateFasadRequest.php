<?php

namespace App\Http\Requests\fifthyformats;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFasadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // adjust if you use policies
    }

    public function rules(): array
    {
        return [
            'name'             => ['sometimes', 'required', 'string'],
            'nick_name'        => ['sometimes', 'nullable', 'string'],
            'father_name'      => ['sometimes', 'nullable', 'string'],

            'main_province'    => ['sometimes', 'nullable', 'string'],
            'main_district'    => ['sometimes', 'nullable', 'string'],
            'main_village'     => ['sometimes', 'nullable', 'string'],

            'now_province'     => ['sometimes', 'nullable', 'string'],
            'now_zone'         => ['sometimes', 'nullable', 'string'],
            'now_district'     => ['sometimes', 'nullable', 'string'],
            'now_village'      => ['sometimes', 'nullable', 'string'],

            'specific_place'   => ['sometimes', 'nullable', 'string'],
            'street_number'    => ['sometimes', 'nullable', 'string'],
            'home_number'      => ['sometimes', 'nullable', 'string'],

            'jabha'            => ['sometimes', 'nullable', 'string'],
            'supplier_name'    => ['sometimes', 'nullable', 'string'],
            'responsibility'   => ['sometimes', 'nullable', 'string'],
            'activity_zone'    => ['sometimes', 'nullable', 'string'],
            'friends_number'   => ['sometimes', 'nullable', 'string'],
            'phone_number'     => ['sometimes', 'nullable', 'string'],
            'bond_distance'    => ['sometimes', 'nullable', 'string'],
            'last_band_reason' => ['sometimes', 'nullable', 'string'],
            'band_place'       => ['sometimes', 'nullable', 'string'],
            'now_job'          => ['sometimes', 'nullable', 'string'],

            'resource_code'    => ['sometimes', 'nullable', 'string'],
            'resource_phone'   => ['sometimes', 'nullable', 'string'],
            'resource_id'      => ['sometimes', 'nullable', 'integer', 'exists:resources,id'],

            // 'picture'        => ['sometimes','nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ];
    }
}
