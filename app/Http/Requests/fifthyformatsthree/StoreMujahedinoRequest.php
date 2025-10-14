<?php

namespace App\Http\Requests\fifthyformatsthree;

use Illuminate\Foundation\Http\FormRequest;

class StoreMujahedinoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nickname' => ['nullable','string'],
            'name' => ['required','string'],
            'father_name' => ['nullable','string'],

            'main_province' => ['nullable','string'],
            'main_district' => ['nullable','string'],
            'main_village'  => ['nullable','string'],

            'now_province'      => ['nullable','string'],
            'now_zone'          => ['nullable','string'],
            'now_district'      => ['nullable','string'],
            'now_village'       => ['nullable','string'],
            'now_specific_place'=> ['nullable','string'],
            'now_street_number' => ['nullable','string'],
            'now_home_number'   => ['nullable','string'],

            'duty_type'   => ['nullable','string'],
            'now_job'     => ['nullable','string'],
            'organ'       => ['nullable','string'],
            'phone_number'=> ['nullable','string'],

            'note'        => ['nullable','string'],
            'resource_id' => ['nullable','integer','exists:resources,id'],
        ];
    }
}
