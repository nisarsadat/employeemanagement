<?php

namespace App\Http\Requests\fifthyformatone;

use Illuminate\Foundation\Http\FormRequest;

class StoreNonResponsablePeopleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'               => ['required','string','max:255'],
            'nick_name'          => ['nullable','string','max:255'],
            'father_name'        => ['nullable','string','max:255'],
            'main_province'      => ['nullable','string','max:255'],
            'main_district'      => ['nullable','string','max:255'],
            'main_village'       => ['nullable','string','max:255'],
            'now_province'       => ['nullable','string','max:255'],
            'now_zone'           => ['nullable','string','max:255'],
            'now_district'       => ['nullable','string','max:255'],
            'now_village'        => ['nullable','string','max:255'],
            'now_specific_place' => ['nullable','string','max:255'],
            'now_street_number'  => ['nullable','string','max:255'],
            'now_home_number'    => ['nullable','string','max:255'],
            'friends_quantity'   => ['nullable','string','max:255'],
            'supplier'           => ['nullable','string','max:255'],
            'phone_number'       => ['nullable','string','max:100'],
            'history_jorm'       => ['nullable','string'],
            'activity_place'     => ['nullable','string','max:255'],
            'old_job'            => ['nullable','string','max:255'],
            'now_job'            => ['nullable','string','max:255'],
            'gun_type'           => ['nullable','string','max:255'],
            'tanzim'             => ['nullable','string','max:255'],
            'takhalof_type'      => ['nullable','string','max:255'],
            'note'               => ['nullable','string'],
            'Province_addr'             => ['nullable','string'],

            'resource_id'        => ['nullable','integer','exists:resources,id'],
        ];
    }
}
