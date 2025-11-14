<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCheckpointRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'checkpoint_name'   => ['sometimes','nullable','string'],
            'employee_quantity' => ['sometimes','nullable','integer'],

            'organ'             => ['sometimes','nullable','string'],
            'create_reason'     => ['sometimes','nullable','string'],

            'officer_name'      => ['sometimes','nullable','string'],
            'officer_f_name'    => ['sometimes','nullable','string'],
            'officer_phone'     => ['sometimes','nullable','string'],

            'gun_type'          => ['sometimes','nullable','string'],

            'province'          => ['sometimes','nullable','string'],
            'zone'              => ['sometimes','nullable','string'],
            'district'          => ['sometimes','nullable','string'],
            'village'           => ['sometimes','nullable','string'],
            'specefic_place'    => ['sometimes','nullable','string'],

            'coordinates'       => ['sometimes','nullable','string'],
            'Province_addr'         => ['sometimes','nullable','string'],

            'resource_id'       => ['sometimes','nullable','integer','exists:resources,id'],
            'note'              => ['sometimes','nullable','string'],
        ];
    }
}
