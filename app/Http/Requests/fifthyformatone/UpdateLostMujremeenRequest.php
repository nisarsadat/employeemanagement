<?php

namespace App\Http\Requests\fifthyformatone;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLostMujremeenRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'               => ['sometimes','required','string','max:255'],
            'picture'            => ['nullable','image'],
            'nickname'           => ['nullable','string','max:255'],
            'father_name'        => ['nullable','string','max:255'],

            'main_province'      => ['nullable','string','max:255'],
            'main_district'      => ['nullable','string','max:255'],
            'main_village'       => ['nullable','string','max:255'],

            'now_province'       => ['nullable','string','max:255'],
            'now_district'       => ['nullable','string','max:255'],
            'now_village'        => ['nullable','string','max:255'],
            'now_specific_place' => ['nullable','string','max:255'],
            'now_street_number'  => ['nullable','string','max:255'],
            'now_home_number'    => ['nullable','string','max:255'],

            'province'           => ['nullable','string','max:255'],
            'zone'               => ['nullable','string','max:255'],
            'district'           => ['nullable','string','max:255'],

            'old_job'            => ['nullable','string','max:255'],

            'jorm'               => ['nullable','string'],
            'history_jorm'       => ['nullable','string'],
            'lost_reason'        => ['nullable','string','max:255'],
            'phone_number'       => ['nullable','string','max:100'],

            'note'               => ['nullable','string'],
            'resource_id'        => ['nullable','integer','exists:resources,id'],
        ];
    }
}
