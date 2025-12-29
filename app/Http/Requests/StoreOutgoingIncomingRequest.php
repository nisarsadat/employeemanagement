<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOutgoingIncomingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number' => ['required', 'string', 'max:255'],

            'outgoing_to_reyasat' => ['nullable', 'string', 'max:255'],
            'out_going_to_others' => ['nullable', 'string', 'max:255'],
            'incomming' => ['nullable', 'string', 'max:255'],

            'ahkam_reyasat' => ['nullable', 'string', 'max:255'],
            'ahkam_rad_shoda_reyasat' => ['nullable', 'string', 'max:255'],
            'reyasat_na_direction' => ['nullable', 'string', 'max:255'],

            'loy_reyasat_ahkam' => ['nullable', 'string', 'max:255'],
            'loy_reyasat_ahakm_rad' => ['nullable', 'string', 'max:255'],
            'loy_reyasat_direction' => ['nullable', 'string', 'max:255'],

            'sign_date' => ['nullable', 'date'],
            'outgoing_incoming_date' => ['nullable', 'date'],

            'marja' => ['nullable', 'string', 'max:255'],
            'who_sign' => ['nullable', 'string', 'max:255'],

            'numer_of_jold' => ['nullable', 'string', 'max:255'],
            'list_number' => ['nullable', 'string', 'max:255'],

            'naql_amount' => ['nullable', 'string', 'max:255'],
            'pages' => ['nullable', 'string', 'max:255'],

            'zamima' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],

            // files
            'documents' => ['nullable', 'array'],
            'documents.*' => ['file', 'max:10240'], // 10MB each
        ];
    }
}
