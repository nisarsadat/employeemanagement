<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShoraRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'shora_village'         => ['sometimes','nullable','string'],
            'note'                  => ['sometimes','nullable','string'],

            'shora_name'            => ['sometimes','nullable','string'],
            'shora_goal'            => ['sometimes','nullable','string'],
            'people_quantity'       => ['sometimes','nullable','integer'],
            'week_meeting_quantity' => ['sometimes','nullable','integer'],
            'regester'              => ['sometimes','nullable','string'],
            'shora_meeting_place'   => ['sometimes','nullable','string'],

            'responsible_name'      => ['sometimes','nullable','string'],
            'responsible_f_name'    => ['sometimes','nullable','string'],
            'responsible_job'       => ['sometimes','nullable','string'],
            'responsible_phone'     => ['sometimes','nullable','string'],

            'supplier_name'         => ['sometimes','nullable','string'],
            'supplier_job'          => ['sometimes','nullable','string'],
            'supplier_phone'        => ['sometimes','nullable','string'],

            'emarat_tarfdary'       => ['sometimes','nullable','string'],

            'shora_province'        => ['sometimes','nullable','string'],
            'shora_zone'            => ['sometimes','nullable','string'],
            'shora_district'        => ['sometimes','nullable','string'],

            'resource_id'           => ['sometimes','nullable','integer','exists:resources,id'],
        ];
    }
}
