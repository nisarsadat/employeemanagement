<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInformationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'resource_id'        => ['sometimes','nullable','integer','exists:resources,id'],
            'office_employee_id' => ['sometimes','nullable','integer','exists:office_employees,id'],

            'name'              => ['sometimes','nullable','string'],
            'nick_name'         => ['sometimes','nullable','string'],
            'father_name'       => ['sometimes','nullable','string'],
            'grand_father_name' => ['sometimes','nullable','string'],

            'main_province'  => ['sometimes','nullable','string'],
            'main_districts' => ['sometimes','nullable','string'],
            'main_village'   => ['sometimes','nullable','string'],

            'now_province' => ['sometimes','nullable','string'],
            'now_district' => ['sometimes','nullable','string'],
            'now_village'  => ['sometimes','nullable','string'],

            'syasi_info_type'   => ['sometimes','nullable','string'],
            'jenayee_info_type' => ['sometimes','nullable','string'],

            'information_saved_date' => ['sometimes','nullable','string'],
            'information_saved_hour' => ['sometimes','nullable','string'],
            'information_degree'     => ['sometimes','nullable','string'],

            'tafseel'         => ['sometimes','nullable','string'],
            'manager_comment' => ['sometimes','nullable','string'],
            'lead_drirection' => ['sometimes','nullable','string'],
            'talks'           => ['sometimes','nullable','string'],
            'note'            => ['sometimes','nullable','string'],
            'result'            => ['sometimes','nullable','string'],
            'Province_addr' => ['nullable','string'],

            'documents'   => ['nullable','array'],
            'documents.*' => ['file'],
        ];
    }
}
