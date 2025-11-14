<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInformationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'resource_id'        => ['nullable','integer','exists:resources,id'],
            'office_employee_id' => ['nullable','integer','exists:office_employees,id'],

            'name'              => ['required','string'],
            'nick_name'         => ['nullable','string'],
            'father_name'       => ['nullable','string'],
            'grand_father_name' => ['nullable','string'],

            'main_province'  => ['nullable','string'],
            'main_districts' => ['nullable','string'],
            'main_village'   => ['nullable','string'],

            'now_province' => ['nullable','string'],
            'now_district' => ['nullable','string'],
            'now_village'  => ['nullable','string'],

            'syasi_info_type'   => ['nullable','string'],
            'jenayee_info_type' => ['nullable','string'],

            'information_saved_date' => ['nullable','string'],
            'information_saved_hour' => ['nullable','string'],
            'information_degree'     => ['nullable','string'],

            'tafseel'         => ['nullable','string'],
            'manager_comment' => ['nullable','string'],
            'lead_drirection' => ['nullable','string'],
            'talks'           => ['nullable','string'],
            'note'            => ['nullable','string'],
            'Province_addr' => ['nullable','string'],

            'documents'   => ['nullable','array'],
            'documents.*' => ['file'],
        ];
    }
}
