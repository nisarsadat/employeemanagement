<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncomeOilRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array {
    return [
        'oil_account_id' => ['required','exists:oil_accounts,id'],
        'date'           => ['required','date'],           // <-- NEW
        'oil_quantity'   => ['required','numeric','min:0.001'],
        'source'         => ['nullable','string','max:255'],
        'note'           => ['nullable','string'],
    ];
}

}
