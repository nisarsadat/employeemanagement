<?php

namespace App\Http\Requests\fifthyformatsthree;

use Illuminate\Foundation\Http\FormRequest;

class StoreSarayanoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'saray_name' => ['required','string'],
            'saray_type' => ['nullable','string'],

            'responsible_name' => ['nullable','string'],
            'responsible_f_name' => ['nullable','string'],
            'responsible_position' => ['nullable','string'],
            'responsible_phone' => ['nullable','string'],

            'province' => ['nullable','string'],
            'zone' => ['nullable','string'],
            'district' => ['nullable','string'],
            'village' => ['nullable','string'],
            'specific_place' => ['nullable','string'],
            'cordenates' => ['nullable','string'],

            'employee_quantity' => ['nullable','string'],
            'note' => ['nullable','string'],
            'Province_addr' => ['nullable','string'],

            'resource_id' => ['nullable','integer','exists:resources,id'],
        ];
    }
}
