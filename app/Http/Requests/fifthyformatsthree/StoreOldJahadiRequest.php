<?php

namespace App\Http\Requests\fifthyformatsthree;

use Illuminate\Foundation\Http\FormRequest;

class StoreOldJahadiRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nick_name' => ['nullable','string'],
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

            'old_job' => ['nullable','string'],
            'now_job' => ['nullable','string'],
            'employee_quantity' => ['nullable','string'],
            'supplier' => ['nullable','string'],
            'tanzim' => ['nullable','string'],
            'Province_addr' => ['nullable','string'],

            'note' => ['nullable','string'],
            'coordinates' => ['nullable','string'],
            'resource_id' => ['nullable','integer','exists:resources,id'],
        ];
    }
}
