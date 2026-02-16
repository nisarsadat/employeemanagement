<?php

namespace App\Http\Requests\fifthyformatsthree;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHostelRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'hostel_name' => ['sometimes','required','string'],
            'hostel_type' => ['nullable','string'],
            'people_quantity' => ['nullable','string'],
            'people_type' => ['nullable','string'],
            'picture' => ['nullable','image'],

            'responsible_name'     => ['nullable','string'],
            'responsible_f_name'   => ['nullable','string'],
            'responsible_position' => ['nullable','string'],
            'responsible_phone'    => ['nullable','string'],

            'province'       => ['nullable','string'],
            'zone'           => ['nullable','string'],
            'district'       => ['nullable','string'],
            'village'        => ['nullable','string'],
            'specific_place' => ['nullable','string'],
            'coordenates'    => ['nullable','string'],
            'Province_addr' => ['nullable','string'],

            'note'         => ['nullable','string'],
            'resource_id'  => ['nullable','integer','exists:resources,id'],
        ];
    }
}
