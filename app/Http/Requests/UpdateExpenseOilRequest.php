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
            // ✅ REQUIRED relations (important!)
            'oil_account_id'  => ['required', 'integer', 'exists:oil_accounts,id'],
            'employee_id'     => ['nullable', 'integer', 'exists:office_employees,id'],

            // ✅ REQUIRED core fields
            'name'            => ['required', 'string'],
            'liter_in_number' => ['required', 'numeric', 'min:0.001'],

            // ✅ optional fields
            'father_name'     => ['nullable', 'string'],
            'payer'           => ['nullable', 'string'],
            'date'            => ['nullable', 'date'],

            'liter_in_words'  => ['nullable', 'string'],
            'tafseel'         => ['nullable', 'string'],
            'note'            => ['nullable', 'string'],
        ];
    }
}
