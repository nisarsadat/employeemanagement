<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report_note' => ['required', 'string'],
            'type' => ['required', 'string', Rule::in(['daily', 'weekly', 'monthly', 'quarter', 'yearly'])],
        ];
    }
}
