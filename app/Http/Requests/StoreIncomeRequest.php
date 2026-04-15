<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncomeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'account_id'  => ['required','exists:accounts,id'],
            'amount'      => ['required','numeric','min:0.01'],
            'amount_by_word'      => ['required','string'],
            'received_at' => ['required','date'],
            'note'        => ['nullable','string'],
        ];
    }
}

