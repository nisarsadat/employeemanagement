<?php

namespace App\Http\Requests\fifthyformatsthree;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmaratOfficeRequest extends FormRequest
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
            'coordenates'     => ['nullable','string'],
            'home_type'       => ['nullable','string'],
            'responsible_office'=> ['nullable','string'],
            'tahdid_mutawajeh'=> ['nullable','string'],
            'guard_quantity'  => ['nullable','string'],
            'note'            => ['nullable','string'],
            'resource_id'     => ['nullable','integer','exists:resources,id'],
            'Province_addr' => ['nullable','string'],
            'picture' => ['nullable','image'],

        ];
    }
}
