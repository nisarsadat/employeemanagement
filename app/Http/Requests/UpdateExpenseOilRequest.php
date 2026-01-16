<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseOilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'oil_account_id'   => ['sometimes', 'required', 'integer', 'exists:oil_accounts,id'],
            'employee_id'      => ['sometimes', 'nullable', 'integer', 'exists:office_employees,id'],

            'name'             => ['sometimes', 'nullable', 'string', 'max:255'],
            'father_name'      => ['sometimes', 'nullable', 'string', 'max:255'],
            'date'             => ['sometimes', 'nullable', 'date'],

            'liter_in_number'  => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'liter_in_words'   => ['sometimes', 'nullable', 'string', 'max:255'],

            'account_name'     => ['sometimes', 'nullable', 'string', 'max:255'],
            'tafseel'          => ['sometimes', 'nullable', 'string'],
            'payer'            => ['sometimes', 'nullable', 'string', 'max:255'],
            'note'             => ['sometimes', 'nullable', 'string'],
        ];
    }
}
