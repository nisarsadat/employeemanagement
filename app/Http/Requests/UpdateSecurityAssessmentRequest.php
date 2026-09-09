<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSecurityAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'place_name' => [
                'required',
                'string',
                'max:255',
            ],

            'responsible_owner_name' => [
                'required',
                'string',
                'max:255',
            ],

            'province' => [
                'required',
                'string',
                'max:255',
            ],

            'district' => [
                'required',
                'string',
                'max:255',
            ],

            'specific_place' => [
                'required',
                'string',
                'max:255',
            ],

            'coordinates' => [
                'required',
                'string',
                'max:255',
            ],

            'direction' => [
                'required',
                'in:north,south,east,west',
            ],

            'daily_visitors_count' => [
                'required',
                'integer',
                'min:0',
                'max:1000000',
            ],

            'risk_places' => [
                'required',
                'string',
            ],

            'vulnerable_places' => [
                'required',
                'string',
            ],

            'security_gaps' => [
                'required',
                'string',
            ],

            'weak_points' => [
                'required',
                'string',
            ],

            'security_incidents' => [
                'required',
                'string',
            ],

            'entrance_gate' => [
                'required',
                'string',
                'max:255',
            ],

            'exit_gate' => [
                'required',
                'string',
                'max:255',
            ],

            'emergency_gate' => [
                'required',
                'string',
                'max:255',
            ],

            'secondary_gate' => [
                'required',
                'string',
                'max:255',
            ],

            'physical_check_status' => [
                'required',
                'string',
                'max:255',
            ],

            'technical_check_status' => [
                'required',
                'string',
                'max:255',
            ],

            'security_cameras' => [
                'required',
                'string',
                'max:255',
            ],

            'guards_count' => [
                'required',
                'integer',
                'min:0',
                'max:1000000',
            ],

            'active_towers_count' => [
                'required',
                'integer',
                'min:0',
                'max:1000000',
            ],

            'active_guard_posts_count' => [
                'required',
                'integer',
                'min:0',
                'max:1000000',
            ],

            'security_responsible_name' => [
                'required',
                'string',
                'max:255',
            ],

            'security_responsible_phone' => [
                'required',
                'string',
                'max:30',
                'regex:/^[0-9+\-\s()]+$/',
            ],

            'other_problems' => [
                'required',
                'string',
            ],

            'note' => [
                'required',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'این فیلد الزامی است.',

            'direction.in' =>
                'سمت انتخاب شده معتبر نیست.',

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
