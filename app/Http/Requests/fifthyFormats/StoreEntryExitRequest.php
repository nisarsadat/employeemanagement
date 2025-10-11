<?php

namespace App\Http\Requests\fifthyFormats;

use Illuminate\Foundation\Http\FormRequest;

class StoreEntryExitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'resource_id' => ['nullable','integer','exists:resources,id'],

            'entry_way' => ['nullable','string','max:255'],
            'way_type' => ['nullable','string','max:255'],
            'province' => ['nullable','string','max:255'],
            'zone' => ['nullable','string','max:255'],
            'district' => ['nullable','string','max:255'],
            'village' => ['nullable','string','max:255'],
            'specific_place' => ['nullable','string','max:255'],
            'cordinate' => ['nullable','string','max:255'],
            'amnayaty_poste' => ['nullable','string','max:255'],
            'amnayaty_ishala' => ['nullable','string','max:255'],
            'where_went' => ['nullable','string','max:255'],
            'where_come' => ['nullable','string','max:255'],
            'notes' => ['nullable','string'],
        ];
    }
}
