<?php

namespace App\Http\Requests\fifthyformatstow;

use Illuminate\Foundation\Http\FormRequest;

class StoreHadoBusStopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => ['required','string'],
            'hada_type'  => ['nullable','string'],
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
            'street_number'  => ['nullable','string'],
            'coordinates'    => ['nullable','string'],
            'Province_addr' => ['nullable','string'],

            'note'        => ['nullable','string'],
            'resource_id' => ['nullable','integer','exists:resources,id'],
        ];
    }
}
