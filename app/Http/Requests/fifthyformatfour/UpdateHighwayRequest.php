<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHighwayRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'high_way_name'   => ['sometimes','nullable','string'],
            'province'        => ['sometimes','nullable','string'],
            'zone'            => ['sometimes','nullable','string'],
            'district'        => ['sometimes','nullable','string'],
            'village'         => ['sometimes','nullable','string'],
            'specific_place'  => ['sometimes','nullable','string'],
            'coordinates'     => ['sometimes','nullable','string'],
            'resource_id'     => ['sometimes','nullable','integer','exists:resources,id'],
            'note'            => ['sometimes','nullable','string'],
        ];
    }
}
