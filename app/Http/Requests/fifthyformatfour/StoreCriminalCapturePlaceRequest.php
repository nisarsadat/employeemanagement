<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class StoreCriminalCapturePlaceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'                => ['nullable','string'],
            'father_name'         => ['nullable','string'],
            'job'                 => ['nullable','string'],

            'jorm_type'           => ['nullable','string'],
            'criminals_quantity'  => ['nullable','integer'],
            'office'              => ['nullable','string'],

            'province'            => ['nullable','string'],
            'zone'                => ['nullable','string'],
            'district'            => ['nullable','string'],
            'village'             => ['nullable','string'],
            'specefic_place'      => ['nullable','string'],

            'coordinates'         => ['nullable','string'],
            'Province_addr'                      => ['nullable','string'],


            'resource_id'         => ['nullable','integer','exists:resources,id'],
            'note'                => ['nullable','string'],
        ];
    }
}
