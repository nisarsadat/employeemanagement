<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'picture' => ['nullable', 'image'],

            // intro
            'name' => $isUpdate
                ? [Rule::excludeIf(true)]
                : ['required', 'string'],

            'father_name' => $isUpdate
                ? [Rule::excludeIf(true)]
                : ['nullable', 'string'],

            'grand_father_name' => $isUpdate
                ? [Rule::excludeIf(true)]
                : ['nullable', 'string'],

            'nick_name' => ['nullable', 'string'],
            'code_number' => ['nullable', 'string'],
            'tazkira' => ['nullable', 'string'],

            // main address
            'main_province' => $isUpdate
                ? [Rule::excludeIf(true)]
                : ['nullable', 'string'],

            'main_district' => $isUpdate
                ? [Rule::excludeIf(true)]
                : ['nullable', 'string'],

            'main_village' => $isUpdate
                ? [Rule::excludeIf(true)]
                : ['nullable', 'string'],

            // current address
            'now_province' => ['nullable', 'string'],
            'now_district' => ['nullable', 'string'],
            'now_village' => ['nullable', 'string'],
            'now_specific_place' => ['nullable', 'string'],
            'now_street_number' => ['nullable', 'string'],
            'now_home_number' => ['nullable', 'string'],
            'letry_format_name' => ['nullable', 'string'],

            // jobs
            'old_job' => ['nullable', 'string'],
            'now_job' => ['nullable', 'string'],
            'job_place' => ['nullable', 'string'],

            // status & education
            'activity' => ['nullable', 'in:فعال,غیر فعال'],
            'halat_madani' => ['nullable', 'in:مجرد,متاهل'],
            'age' => ['nullable', 'string'],
            'elmi_level' => ['nullable', 'string'],
            'faculty' => ['nullable', 'string'],
            'specific_skills' => ['nullable', 'string'],
            'train' => ['nullable', 'string'],
            'trust_percentage' => ['nullable', 'string'],
            'blood_group' => ['nullable', 'string'],

            // misc
            'resource_saved_date' => ['nullable', 'string'],
            'resource_activity_type' => ['nullable', 'string'],

            'office_employee_id' => $isUpdate
                ? [Rule::excludeIf(true)]
                : ['nullable', 'exists:office_employees,id'],

            'resource_cover' => ['nullable', 'string'],
            'language' => ['nullable', 'string'],
            'nation' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'phone_number_1' => ['nullable', 'string', 'max:20'],
            'phone_number_2' => ['nullable', 'string', 'max:20'],
            'coordinates' => ['nullable', 'string'],
            'resedint_code' => ['nullable', 'string'],
            'note' => ['nullable', 'string'],
            'zone' => ['nullable', 'string'],
            'hawza' => ['nullable', 'string'],
            'specefic_place' => ['nullable', 'string'],
            'project' => ['nullable', 'string'],
            'Province_addr' => ['nullable', 'string'],
            'format_owner_type' => ['nullable', 'string'],
            'format_owner_id' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            // no prohibited messages needed anymore,
            // because these fields are silently ignored on update
        ];
    }
}