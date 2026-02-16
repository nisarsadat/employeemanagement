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
            'office_outgoing_page' => ['nullable','string'],

            'office_incoming_date' => ['nullable','date'],
            'office_incoming_page' => ['nullable','string'],

            'reyasat_outgoing_date' => ['nullable','date'],
            'reyasat_outgoing_page' => ['nullable','string'],

            'reyasat_incomming_date' => ['nullable','date'],
            'reyasat_incomming_page' => ['nullable','string'],

            'loy_reyasat_outgoing_date' => ['nullable','date'],
            'loy_reyasat_outgoing_page' => ['nullable','string'],

            'answer_date' => ['nullable','date'],
            'answer_page' => ['nullable','string'],

            'peshnehad' => ['nullable','date'],
            'outgoing_incoming_date' => ['nullable','date'],

            'who_sighn' => ['nullable','string'],
            'marja' => ['nullable','string'],

            'tafseel' => ['nullable','string'],
            'notes' => ['nullable','string'],

            'page' => ['nullable','string'],
            'dusya' => ['nullable','string'],
            'fihrist_page' => ['nullable','string'],

            'naqal_count' => ['nullable','integer','min:0'],
            'awraq' => ['nullable','integer','min:0'],
            'zamima' => ['nullable','integer','min:0'],
        ];
    }
}
