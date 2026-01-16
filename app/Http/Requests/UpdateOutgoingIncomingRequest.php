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
            'office_outgoing_page' => ['sometimes','nullable','string','max:255'],

            'office_incoming_date' => ['sometimes','nullable','date'],
            'office_incoming_page' => ['sometimes','nullable','string','max:255'],

            'reyasat_outgoing_date' => ['sometimes','nullable','date'],
            'reyasat_outgoing_page' => ['sometimes','nullable','string','max:255'],

            'loy_reyasat_outgoing_date' => ['sometimes','nullable','date'],
            'loy_reyasat_outgoing_page' => ['sometimes','nullable','string','max:255'],

            'answer_date' => ['sometimes','nullable','date'],
            'answer_page' => ['sometimes','nullable','string','max:255'],

            'docs_sign_date' => ['sometimes','nullable','date'],
            'outgoing_incoming_date' => ['sometimes','nullable','date'],

            'who_sighn' => ['sometimes','nullable','string','max:255'],
            'marja' => ['sometimes','nullable','string','max:255'],

            'tafseel' => ['sometimes','nullable','string'],
            'notes' => ['sometimes','nullable','string'],

            'page' => ['sometimes','nullable','string','max:255'],
            'dusya' => ['sometimes','nullable','string','max:255'],
            'fihrist_page' => ['sometimes','nullable','string','max:255'],

            'naqal_count' => ['sometimes','nullable','integer','min:0'],
            'awraq' => ['sometimes','nullable','integer','min:0'],
            'zamima' => ['sometimes','nullable','integer','min:0'],
        ];
    }
}
