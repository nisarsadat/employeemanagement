<?php

namespace App\Http\Requests\fifthyformatone;

use Illuminate\Foundation\Http\FormRequest;

class StoreOldSoldiersHomesRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'province'        => ['nullable','string'],
            'zone'            => ['nullable','string'],
            'district'        => ['nullable','string'],
            'village'         => ['nullable','string'],
            'specific_place'  => ['nullable','string'],
            'street_number'   => ['nullable','string'],
            'home_number'     => ['nullable','string'],
            'coordinates'     => ['nullable','string'],
            'name'            => ['required','string'],
            'father_name'     => ['nullable','string'],
            'main_province'   => ['nullable','string'],
            'old_job'         => ['nullable','string'],
            'now_activity'    => ['nullable','string'],
            'note'            => ['nullable','string'],
            'phone_number'    => ['nullable','string'],
            'Province_addr'   => ['nullable','string'],
            'picture' => ['nullable','image'],

            'resource_id'     => ['nullable','integer','exists:resources,id'],
        ];
    }
}
