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
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        $base = [
            'picture' => ['nullable', 'image'],

            // intro
            'name' => $isUpdate ? ['prohibited'] : ['required', 'string'],
            'father_name' => $isUpdate ? ['prohibited'] : ['nullable', 'string'],
            'grand_father_name' => $isUpdate ? ['prohibited'] : ['nullable', 'string'],
            'nick_name' => ['nullable', 'string'],
            'code_number' => ['nullable', 'string'],
            'tazkira' => ['nullable', 'string'],
            'tazblood_groupkira' => ['nullable', 'string'],

            // main address
            'main_province' => $isUpdate ? ['prohibited'] : ['nullable', 'string'],
            'main_district' => $isUpdate ? ['prohibited'] : ['nullable', 'string'],
            'main_village' => $isUpdate ? ['prohibited'] : ['nullable', 'string'],

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
            'percentage' => ['nullable', 'string'],

            // misc
            'resource_saved_date' => ['nullable', 'string'],
            'resource_activity_type' => ['nullable', 'string'],
            'office_employee_id' => $isUpdate ? ['prohibited'] : ['nullable', 'exists:office_employees,id'],
            'resource_cover' => ['nullable', 'string'],
            'language' => ['nullable', 'string'],
            'nation' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string'],
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
        ];

        if ($isUpdate) {
            foreach ($base as $k => &$rules) {
                if (!in_array('prohibited', $rules, true)) {
                    array_unshift($rules, 'sometimes');
                }
            }
        }

        return $base;
    }

    public function messages(): array
    {
        return [
            'name.prohibited' => 'د نوم ساحه د ثبت وروسته نه شي بدلېدای.',
            'father_name.prohibited' => 'د پلار نوم ساحه د ثبت وروسته نه شي بدلېدای.',
            'grand_father_name.prohibited' => 'د نيکه نوم ساحه د ثبت وروسته نه شي بدلېدای.',
            'main_province.prohibited' => 'د اصلي ولايت ساحه د ثبت وروسته نه شي بدلېدای.',
            'main_district.prohibited' => 'د اصلي ولسوالۍ ساحه د ثبت وروسته نه شي بدلېدای.',
            'main_village.prohibited' => 'د اصلي کلي ساحه د ثبت وروسته نه شي بدلېدای.',
            'office_employee_id.prohibited' => 'د مربوط کارمند ساحه د ثبت وروسته نه شي بدلېدای.',
        ];
    }
}