<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCriminalCapturePlaceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'                => ['sometimes','nullable','string'],
            'father_name'         => ['sometimes','nullable','string'],
            'job'                 => ['sometimes','nullable','string'],

            'jorm_type'           => ['sometimes','nullable','string'],
            'criminals_quantity'  => ['sometimes','nullable','integer'],
            'office'              => ['sometimes','nullable','string'],

            'province'            => ['sometimes','nullable','string'],
            'zone'                => ['sometimes','nullable','string'],
            'district'            => ['sometimes','nullable','string'],
            'village'             => ['sometimes','nullable','string'],
            'specefic_place'      => ['sometimes','nullable','string'],

            'coordinates'         => ['sometimes','nullable','string'],
            'Province_addr'         => ['sometimes','nullable','string'],

            'resource_id'         => ['sometimes','nullable','integer','exists:resources,id'],
            'note'                => ['sometimes','nullable','string'],
        ];
    }
}
