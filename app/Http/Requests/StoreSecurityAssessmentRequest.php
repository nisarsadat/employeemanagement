<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSecurityAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'place_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'responsible_owner_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'province' => [
                'nullable',
                'string',
                'max:255',
            ],

            'district' => [
                'nullable',
                'string',
                'max:255',
            ],

            'specific_place' => [
                'nullable',
                'string',
                'max:255',
            ],

            'coordinates' => [
                'nullable',
                'string',
                'max:255',
            ],

            'north' => [
                'nullable',
                'string',
            ],

            'south' => [
                'nullable',
                'string',
            ],

            'east' => [
                'nullable',
                'string',
            ],

            'west' => [
                'nullable',
                'string',
            ],

            'daily_visitors_count' => [
                'nullable',
                'integer',
                'min:0',
                'max:1000000',
            ],

            'risk_places' => [
                'nullable',
                'string',
            ],

            'vulnerable_places' => [
                'nullable',
                'string',
            ],

            'security_gaps' => [
                'nullable',
                'string',
            ],

            'weak_points' => [
                'nullable',
                'string',
            ],

            'security_incidents' => [
                'nullable',
                'string',
            ],

            'entrance_gate' => [
                'nullable',
                'string',
                'max:255',
            ],

            'exit_gate' => [
                'nullable',
                'string',
                'max:255',
            ],

            'emergency_gate' => [
                'nullable',
                'string',
                'max:255',
            ],

            'secondary_gate' => [
                'nullable',
                'string',
                'max:255',
            ],

            'physical_check_status' => [
                'nullable',
                'string',
                'max:255',
            ],

            'technical_check_status' => [
                'nullable',
                'string',
                'max:255',
            ],

            'security_cameras' => [
                'nullable',
                'string',
                'max:255',
            ],

            'guards_count' => [
                'nullable',
                'integer',
                'min:0',
                'max:1000000',
            ],

            'active_towers_count' => [
                'nullable',
                'integer',
                'min:0',
                'max:1000000',
            ],

            'active_guard_posts_count' => [
                'nullable',
                'integer',
                'min:0',
                'max:1000000',
            ],

            'security_responsible_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'security_responsible_phone' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[\p{L}\p{N}+\-\s()]+$/u',
            ],

            'other_problems' => [
                'nullable',
                'string',
            ],

            'note' => [
                'nullable',
                'string',
            ],

            'documents' => [
                'nullable',
                'array',
            ],

            'documents.*' => [
                'file',
                'max:20480',
            ],

            // ✅ ترسره کوونکي (one or more office employees)
            'office_employee_ids' => [
                'nullable',
                'array',
            ],

            'office_employee_ids.*' => [
                'integer',
                'exists:office_employees,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'این فیلد الزامی است.',

            'daily_visitors_count.integer' =>
                'تعداد روزانه مراجعین باید عدد باشد.',

            'daily_visitors_count.min' =>
                'تعداد روزانه مراجعین نمی‌تواند منفی باشد.',

            'guards_count.integer' =>
                'تعداد محافظین باید عدد باشد.',

            'guards_count.min' =>
                'تعداد محافظین نمی‌تواند منفی باشد.',

            'active_towers_count.integer' =>
                'تعداد برج‌های فعال باید عدد باشد.',

            'active_towers_count.min' =>
                'تعداد برج‌های فعال نمی‌تواند منفی باشد.',

            'active_guard_posts_count.integer' =>
                'تعداد قرارگاه‌های فعال باید عدد باشد.',

            'active_guard_posts_count.min' =>
                'تعداد قرارگاه‌های فعال نمی‌تواند منفی باشد.',

            'security_responsible_phone.regex' =>
                'شماره تماس نامعتبر است.',
        ];
    }
}
