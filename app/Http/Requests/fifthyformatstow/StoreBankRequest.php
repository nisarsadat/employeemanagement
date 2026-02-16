<?php

namespace App\Http\Requests\fifthyformatstow;

use Illuminate\Foundation\Http\FormRequest;

class StoreBankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_name'  => ['required','string'],
            'bank_type'  => ['nullable','string'],
            'bank_owner' => ['nullable','string'],
            'picture' => ['nullable','image'],

            'responsible_name'     => ['nullable','string'],
            'responsible_f_name'   => ['nullable','string'],
            'responsible_position' => ['nullable','string'],
            'responsible_phone'    => ['nullable','string'],

            'province'       => ['nullable','string'],
            'zone'           => ['nullable','string'],
            'district'       => ['nullable','string'],
            'village'        => ['nullable','string'],
            'specific_place' => ['nullable','string'],
            'coordinates'    => ['nullable','string'],
            'which_offices_sallary'    => ['nullable','string'],

            'guard_quantity' => ['nullable','integer','min:0'],
            'note'           => ['nullable','string'],
            'resource_id'    => ['nullable','integer','exists:resources,id'],
            'Province_addr' => ['nullable','string'],

        ];
    }
}
