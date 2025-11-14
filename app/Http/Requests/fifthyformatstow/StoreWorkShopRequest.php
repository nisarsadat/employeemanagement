<?php

namespace App\Http\Requests\fifthyformatstow;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workshop_name'  => ['required','string'],
            'workshop_type'  => ['nullable','string'],

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
            'Province_addr' => ['nullable','string'],

            'workers_quantity' => ['nullable','integer','min:0'],
            'note'             => ['nullable','string'],
            'resource_id'      => ['nullable','integer','exists:resources,id'],
        ];
    }
}
