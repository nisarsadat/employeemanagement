<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $base = [
            'picture' => ['nullable', 'image'],

            'name' => ['required', 'string'],
            'father_name' => ['nullable', 'string'],
            'grand_father_name' => ['nullable', 'string'],
            'nickname' => ['nullable', 'string'],
            'tazkira' => ['nullable', 'string'],
            'code_number' => ['nullable', 'string'],

            'main_province' => ['nullable', 'string'],
            'main_district' => ['nullable', 'string'],
            'main_village' => ['nullable', 'string'],

            'now_province' => ['nullable', 'string'],
            'now_district' => ['nullable', 'string'],
            'now_village' => ['nullable', 'string'],

            'old_job' => ['nullable', 'string'],
            'new_job' => ['nullable', 'string'],
            'job_place' => ['nullable', 'string'],

            'zone' => ['nullable', 'string'],
            'hawza' => ['nullable', 'string'],
            'specific_place' => ['nullable', 'string'],

            'phone_number' => ['nullable', 'string'],
            'activity' => ['nullable', 'in:فعال,غیر فعال'],
            'halat_madani' => ['nullable', 'in:مجرد,متاهل'],
            'age' => ['nullable', 'string'],
            'study_status' => ['nullable', 'string'],
            'resource_saved_date' => ['nullable', 'string'],
            'resource_activity_type' => ['nullable', 'string'],
            'resource_cover' => ['nullable', 'string'],
            'language' => ['nullable', 'string'],
            'nation' => ['nullable', 'string'],
            'project' => ['nullable', 'string'],

            'resedint_id' => ['nullable', 'exists:resedints,id'],
            'officeemployee_id' => ['nullable', 'exists:office_employees,id'],

            'notes' => ['nullable', 'string'],
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            // make fields optional on update
            foreach ($base as $k => &$rules) {
                array_unshift($rules, 'sometimes');
            }
        }

        return $base;
    }
}
