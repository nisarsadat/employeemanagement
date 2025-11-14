<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckpointRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'checkpoint_name'   => ['nullable','string'],
            'employee_quantity' => ['nullable','integer'],

            'organ'             => ['nullable','string'],
            'create_reason'     => ['nullable','string'],

            'officer_name'      => ['nullable','string'],
            'officer_f_name'    => ['nullable','string'],
            'officer_phone'     => ['nullable','string'],

            'gun_type'          => ['nullable','string'],

            'province'          => ['nullable','string'],
            'zone'              => ['nullable','string'],
            'district'          => ['nullable','string'],
            'village'           => ['nullable','string'],
            'specefic_place'    => ['nullable','string'],

            'coordinates'       => ['nullable','string'],
            'Province_addr'       => ['nullable','string'],

            'resource_id'       => ['nullable','integer','exists:resources,id'],
            'note'              => ['nullable','string'],
        ];
    }
}
