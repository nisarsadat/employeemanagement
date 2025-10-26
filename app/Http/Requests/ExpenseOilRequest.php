<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseOilRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array {
        return [
            'oil_account_id' => ['required','exists:oil_accounts,id'],
            'date'           => ['required','date'],           // <-- NEW
            'name'           => ['required','string','max:255'],
            'destination'    => ['nullable','string','max:255'],
            'oil_quantity'   => ['required','numeric','min:0.001'],
            'note'           => ['nullable','string'],
        ];
    }

}
