<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOutgoingIncomingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'docs_type' => ['sometimes','nullable','string'],

            'office_outgoing_date' => ['sometimes','nullable','date'],
            'office_outgoing_page' => ['sometimes','nullable','string'],

            'office_incoming_date' => ['sometimes','nullable','date'],
            'office_incoming_page' => ['sometimes','nullable','string'],

            'reyasat_outgoing_date' => ['sometimes','nullable','date'],
            'reyasat_outgoing_page' => ['sometimes','nullable','string'],
            
            'reyasat_incomming_date' => ['sometimes','nullable','date'],
            'reyasat_incomming_page' => ['sometimes','nullable','string'],

            'loy_reyasat_outgoing_date' => ['sometimes','nullable','date'],
            'loy_reyasat_outgoing_page' => ['sometimes','nullable','string'],

            'answer_date' => ['sometimes','nullable','date'],
            'answer_page' => ['sometimes','nullable','string'],

            'peshnehad' => ['sometimes','nullable','string'],
            'documents' => ['sometimes','nullable','array'],
            'documents.*' => ['file','max:20480'],
            'outgoing_incoming_date' => ['sometimes','nullable','date'],

            'who_sighn' => ['sometimes','nullable','string'],
            'marja' => ['sometimes','nullable','string'],

            'tafseel' => ['sometimes','nullable','string'],
            'notes' => ['sometimes','nullable','string'],

            'page' => ['sometimes','nullable','string'],
            'dusya' => ['sometimes','nullable','string'],
            'fihrist_page' => ['sometimes','nullable','string'],

            'naqal_count' => ['sometimes','nullable','integer','min:0'],
            'awraq' => ['sometimes','nullable','integer','min:0'],
            'zamima' => ['sometimes','nullable','integer','min:0'],
        ];
    }
}
