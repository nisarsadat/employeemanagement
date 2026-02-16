<?php

namespace App\Http\Requests\fifthyformatstow;

use Illuminate\Foundation\Http\FormRequest;

class StoreHosppitalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required','string'],
            'specific_past' => ['nullable','string'],
            'hospita_type'  => ['nullable','string'],
            'picture' => ['nullable','image'],

            'responsible_name'     => ['nullable','string'],
            'responsible_position' => ['nullable','string'],
            'responsible_phone'    => ['nullable','string'],
            'responsible_f_name'   => ['nullable','string'],

            'male_quantity'   => ['nullable','integer','min:0'],
            'female_quantity' => ['nullable','integer','min:0'],
            'guard_quantity'  => ['nullable','integer','min:0'],

            'province'       => ['nullable','string'],
            'zone'           => ['nullable','string'],
            'district'       => ['nullable','string'],
            'village'        => ['nullable','string'],
            'specific_place' => ['nullable','string'],
            'street_number'  => ['nullable','string'],
            'coordinates'    => ['nullable','string'],
            'Province_addr' => ['nullable','string'],

            'note'        => ['nullable','string'],
            'resource_id' => ['nullable','integer','exists:resources,id'],
        ];
    }
}
