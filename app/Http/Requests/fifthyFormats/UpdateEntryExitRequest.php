<?php

namespace App\Http\Requests\fifthyFormats;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEntryExitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes','required','string'],
            'resource_id' => ['nullable','integer','exists:resources,id'],

            'entry_way' => ['nullable','string'],
            'way_type' => ['nullable','string'],
            'province' => ['nullable','string'],
            'zone' => ['nullable','string'],
            'district' => ['nullable','string'],
            'village' => ['nullable','string'],
            'specific_place' => ['nullable','string'],
            'cordinate' => ['nullable','string'],
            'amnayaty_poste' => ['nullable','string'],
            'amnayaty_ishala' => ['nullable','string'],
            'where_went' => ['nullable','string'],
            'where_come' => ['nullable','string'],
            'notes' => ['nullable','string'],
            'Province_addr' => ['nullable','string'],

        ];
    }
}
