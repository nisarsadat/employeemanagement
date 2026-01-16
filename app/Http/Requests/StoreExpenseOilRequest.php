<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseOilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'oil_account_id'   => ['required', 'integer', 'exists:oil_accounts,id'],
            'employee_id'      => ['nullable', 'integer', 'exists:office_employees,id'],

            'name'             => ['nullable', 'string', 'max:255'],
            'father_name'      => ['nullable', 'string', 'max:255'],
            'date'             => ['nullable', 'date'],

            'liter_in_number'  => ['nullable', 'numeric', 'min:0'],
            'liter_in_words'   => ['nullable', 'string', 'max:255'],

            'account_name'     => ['nullable', 'string', 'max:255'],
            'tafseel'          => ['nullable', 'string'],
            'payer'            => ['nullable', 'string', 'max:255'],
            'note'             => ['nullable', 'string'],
        ];
    }
}
