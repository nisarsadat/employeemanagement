<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'account_id'    => ['required','exists:accounts,id'],

            // to who pay (OfficeEmployee or Resource)
            'payee_type'    => ['nullable','string', Rule::in(['office_employee','resource'])],
            'payee_id'      => ['nullable','integer'],

            'amount_price'  => ['required','numeric','min:0.01'],
            'paid_at'       => ['required','date'],
            'phone_number'  => ['nullable','string','max:10'],
            'reason_expense'=> ['nullable','string'],
            'note'          => ['nullable','string'],
        ];
    }
}

