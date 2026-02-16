<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class StoreHighwayRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'high_way_name'   => ['nullable','string'],
            'province'        => ['nullable','string'],
            'zone'            => ['nullable','string'],
            'district'        => ['nullable','string'],
            'village'         => ['nullable','string'],
            'specific_place'  => ['nullable','string'],
            'coordinates'     => ['nullable','string'],
            'resource_id'     => ['nullable','integer','exists:resources,id'],
            'note'            => ['nullable','string'],
            'Province_addr'         => ['nullable','string'],
            'picture' => ['nullable','image'],

        ];
    }
}
