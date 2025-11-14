<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class StoreShoraRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'shora_village'         => ['nullable','string'],
            'note'                  => ['nullable','string'],

            'shora_name'            => ['nullable','string'],
            'shora_goal'            => ['nullable','string'],
            'people_quantity'       => ['nullable','integer'],
            'week_meeting_quantity' => ['nullable','integer'],
            'regester'              => ['nullable','string'],
            'shora_meeting_place'   => ['nullable','string'],

            'responsible_name'      => ['nullable','string'],
            'responsible_f_name'    => ['nullable','string'],
            'responsible_job'       => ['nullable','string'],
            'responsible_phone'     => ['nullable','string'],

            'supplier_name'         => ['nullable','string'],
            'supplier_job'          => ['nullable','string'],
            'supplier_phone'        => ['nullable','string'],

            'emarat_tarfdary'       => ['nullable','string'],

            'shora_province'        => ['nullable','string'],
            'shora_zone'            => ['nullable','string'],
            'shora_district'        => ['nullable','string'],
            'Province_addr'         => ['nullable','string'],

            'resource_id'           => ['nullable','integer','exists:resources,id'],
        ];
    }
}
