<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResourceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $base = [
            'picture' => ['nullable','image'], 
            // intro
            'name' => ['required','string'],
            'father_name' => ['nullable','string'],
            'grand_father_name' => ['nullable','string'],
            'nick_name' => ['nullable','string'],
            'code_number' => ['nullable','string'],
            'tazkira' => ['nullable','string'],

            // main address
            'main_province' => ['nullable','string'],
            'main_district' => ['nullable','string'],
            'main_village' => ['nullable','string'],

            // current address
            'now_province' => ['nullable','string'],
            'now_district' => ['nullable','string'],
            'now_village' => ['nullable','string'],
            'now_specific_place' => ['nullable','string'],
            'now_street_number' => ['nullable','string'],
            'now_home_number' => ['nullable','string'],
            'tadarok_place' => ['nullable','string'],

            // jobs
            'old_job' => ['nullable','string'],
            'now_job' => ['nullable','string'],
            'job_place' => ['nullable','string'],

            // status & education
            'activity' => ['nullable','in:فعال,غیر فعال'],
            'halat_madani' => ['nullable','in:مجرد,متاهل'],
            'age' => ['nullable','string'],
            'elmi_level' => ['nullable','string'],
            'faculty' => ['nullable','string'],
            'specific_skills' => ['nullable','string'],
            'train' => ['nullable','string'],
            'percentage' => ['nullable','string'],

            // misc
            'resource_saved_date' => ['nullable','string'],
            'resource_activity_type' => ['nullable','string'],
            'office_employee_id' => ['nullable','exists:office_employees,id'],
            'resource_cover' => ['nullable','string'],
            'language' => ['nullable','string'],
            'nation' => ['nullable','string'],
            'phone_number' => ['nullable','string'],
            'phone_number_1' => ['nullable', 'string', 'max:20'],
            'phone_number_2' => ['nullable', 'string', 'max:20'],
            'coordinates' => ['nullable','string'],
            'resedint_code' => ['nullable' , 'string'],
            'note' => ['nullable','string'],
            'Province_addr' => ['nullable','string'],

        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            foreach ($base as $k => &$rules) array_unshift($rules, 'sometimes');
        }

        return $base;
    }
}
