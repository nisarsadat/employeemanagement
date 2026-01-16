<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOutgoingIncomingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'docs_type' => ['nullable','string'],

            'office_outgoing_date' => ['nullable','date'],
            'office_outgoing_page' => ['nullable','string','max:255'],

            'office_incoming_date' => ['nullable','date'],
            'office_incoming_page' => ['nullable','string','max:255'],

            'reyasat_outgoing_date' => ['nullable','date'],
            'reyasat_outgoing_page' => ['nullable','string','max:255'],

            'loy_reyasat_outgoing_date' => ['nullable','date'],
            'loy_reyasat_outgoing_page' => ['nullable','string','max:255'],

            'answer_date' => ['nullable','date'],
            'answer_page' => ['nullable','string','max:255'],

            'docs_sign_date' => ['nullable','date'],
            'outgoing_incoming_date' => ['nullable','date'],

            'who_sighn' => ['nullable','string','max:255'],
            'marja' => ['nullable','string','max:255'],

            'tafseel' => ['nullable','string'],
            'notes' => ['nullable','string'],

            'page' => ['nullable','string','max:255'],
            'dusya' => ['nullable','string','max:255'],
            'fihrist_page' => ['nullable','string','max:255'],

            'naqal_count' => ['nullable','integer','min:0'],
            'awraq' => ['nullable','integer','min:0'],
            'zamima' => ['nullable','integer','min:0'],
        ];
    }
}
