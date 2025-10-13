<?php

namespace App\Http\Requests\fifthyformatstow;

use Illuminate\Foundation\Http\FormRequest;

class UpdateParkingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parking_name'  => ['sometimes','required','string'],
            'parking_type'  => ['nullable','string'],
            'parking_type_pe' => ['nullable','string'],

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

            'daily_cars_parking_quantity' => ['nullable','string'],
            'note'          => ['nullable','string'],
            'resource_id'   => ['nullable','integer','exists:resources,id'],
        ];
    }
}
