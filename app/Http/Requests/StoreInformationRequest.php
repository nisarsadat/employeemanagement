<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInformationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'resource_id' => ['nullable','integer','exists:resources,id'],

            // ✅ ALL employee references point to office_employees table
            'office_employee_id' => ['nullable','integer','exists:office_employees,id'],
            'office_employee_info_id' => ['nullable','integer','exists:office_employees,id'],

            'name' => ['required','string'],
            'nick_name' => ['nullable','string'],
            'father_name' => ['nullable','string'],
            'grand_father_name' => ['nullable','string'],
            'job' => ['nullable','string'],
            'madani_halat' => ['nullable','string'],
            'age' => ['nullable','string'],
            'tahlil' => ['nullable','string'],
            'estijalyat_degree' => ['nullable','string'],
            'information_category' => ['nullable','string'],

            'hamahangi_date' => ['nullable','string'],
            'maktop_number'  => ['nullable','string'],
            'organ'          => ['nullable','string'],

            'main_province' => ['nullable','string'],
            'main_districts' => ['nullable','string'],
            'main_village' => ['nullable','string'],

            'now_province' => ['nullable','string'],
            'now_district' => ['nullable','string'],
            'now_village' => ['nullable','string'],

            'syasi_info_type' => ['nullable','string'],
            'jenayee_info_type' => ['nullable','string'],

            'information_saved_date' => ['nullable','string'],
            'information_saved_hour' => ['nullable','string'],
            'information_degree' => ['nullable','string'],
            'trust_percentage' => ['nullable','string'],
            'resource_lasrasy' => ['nullable','string'],

            'tafseel' => ['nullable','string'],
            'manager_comment' => ['nullable','string'],
            'lead_drirection' => ['nullable','string'],
            'talks' => ['nullable','string'],
            'note' => ['nullable','string'],
            'docs_note' => ['nullable','string'],
            'result' => ['nullable','string'],
            'Province_addr' => ['nullable','string'],
            'message_to_finance' => ['nullable','string'],
            'money_amount_words'         => ['nullable','string'],
            'finance_status' => ['nullable','string'],
            'finance_paid_at' => ['nullable','string'],
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],

            'documents' => ['nullable','array'],
            'documents.*' => ['file'],
        ];
    }

}
