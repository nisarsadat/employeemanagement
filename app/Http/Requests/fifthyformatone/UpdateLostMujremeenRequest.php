<?php

namespace App\Http\Requests\fifthyformatone;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLostMujremeenRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'picture'     => ['nullable','string'],
            'nick_name'   => ['nullable','string'],
            'name'        => ['sometimes','required','string'],
            'father_name' => ['nullable','string'],

            'main_province' => ['nullable','string'],
            'main_district' => ['nullable','string'],
            'main_village'  => ['nullable','string'],

            'now_province'       => ['nullable','string'],
            'now_district'       => ['nullable','string'],
            'now_village'        => ['nullable','string'],
            'now_specific_place' => ['nullable','string'],
            'now_street_number'  => ['nullable','string'],
            'now_home_number'    => ['nullable','string'],

            'province' => ['nullable','string'],
            'zone'     => ['nullable','string'],
            'district' => ['nullable','string'],

            'old_job'      => ['nullable','string'],
            'jorm'         => ['nullable','string'],
            'history_jorm' => ['nullable','string'],
            'lost_reason'  => ['nullable','string'],

            'phone_number' => ['nullable','string'],

            'note'        => ['nullable','string'],
            'resource_id' => ['nullable','integer','exists:resources,id'],
        ];
    }
}
