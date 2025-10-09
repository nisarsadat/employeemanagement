<?php
// app/Http/Requests/InformationRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InformationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // relations
            'officeemployee_id' => ['nullable','integer','exists:office_employees,id'],
            'zone_employee_id'  => ['nullable','integer','exists:zone_employees,id'],

            // codes (if you keep codes unique add unique rules)
            'information_code'      => ['nullable','string','max:100'],
            'office_employee_code'  => ['nullable','string','max:100'],
            'zone_employee_code'    => ['nullable','string','max:100'],

            // intro
            'name'              => ['required','string'],
            'father_name'       => ['nullable','string'],
            'nickname'          => ['nullable','string'],
            'grand_father_name' => ['nullable','string'],

            // main
            'main_province' => ['nullable','string'],
            'main_district' => ['nullable','string'],
            'main_village'  => ['nullable','string'],

            // now
            'now_province' => ['nullable','string'],
            'now_district' => ['nullable','string'],
            'now_village'  => ['nullable','string'],

            // types
            'seyasi_type'   => ['nullable','string'], // ساعین بالفساد / شر و فساد / ضد جاسوسی
            'jenayee_type'  => ['nullable','string'], // جینایی
            'information_type' => ['nullable','string'],

            'information_date' => ['nullable','string'],
            'information_hour' => ['nullable','string'],

            // text degree + dropdown category
            'importance_degree'   => ['nullable','string'],
            'information_category'=> ['nullable', 'string'],

            // big texts
            'tafseel' => ['nullable','string'],
            'talks'   => ['nullable','string'],
            'outcome' => ['nullable','string'],
            'notes'   => ['nullable','string'],
        ];
    }
}
