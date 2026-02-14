<?php

namespace App\Http\Requests\fifthyformatsthree;

use Illuminate\Foundation\Http\FormRequest;

class StoreMashkokPeopleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'picture'     => ['nullable','image'],
            'name'        => ['required','string'],
            'father_name' => ['nullable','string'],

            'main_province' => ['nullable','string'],
            'main_district' => ['nullable','string'],
            'main_village'  => ['nullable','string'],

            'now_province'       => ['nullable','string'],
            'now_zone'           => ['nullable','string'],
            'now_districts'      => ['nullable','string'],
            'now_village'        => ['nullable','string'],
            'now_specific_place' => ['nullable','string'],
            'now_street_number'  => ['nullable','string'],
            'now_home_number'    => ['nullable','string'],
            'coordinates'        => ['nullable','string'],

            'old_job'           => ['nullable','string'],
            'now_job'           => ['nullable','string'],
            'phone_number'      => ['nullable','string'],
            'mushkokyat_points' => ['nullable','string'],
            'mashkokyat_type' => ['nullable','string'],
            'Province_addr' => ['nullable','string'],

            'note'        => ['nullable','string'],
            'tafseel'        => ['nullable','string'],
            'resource_id' => ['nullable','integer','exists:resources,id'],
        ];
    }
}
