<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMeetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'meeting_title' => [
                'required',
                'string',
                'max:255',
            ],

            'organ' => [
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

            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],

            'total_time' => [
                'required',
                'string',
                'max:100',
            ],

            'participant' => [
                'required',
                'string',
            ],

            'boss_participant' => [
                'required',
                'string',
            ],

            'office_employee_count' => [
                'required',
                'integer',
                'min:0',
                'max:1000000',
            ],

            'leader_name' => [
                'required',
                'string',
                'max:255',
            ],

            'done_actions' => [
                'required',
                'string',
            ],

            'mashkok_situations' => [
                'required',
                'string',
            ],

            'problems' => [
                'required',
                'string',
            ],

            'meeting_supplier' => [
                'required',
                'string',
                'max:255',
            ],

            'meeting_supplier_phone' => [
                'required',
                'string',
                'max:30',
                'regex:/^[0-9+\-\s()]+$/',
            ],

            'meeting_amniyat_responsable' => [
                'required',
                'string',
                'max:255',
            ],

            'meeting_amniyat_responsable_phone' => [
                'required',
                'string',
                'max:30',
                'regex:/^[0-9+\-\s()]+$/',
            ],

            'amniyat_mansobine_count' => [
                'required',
                'integer',
                'min:0',
                'max:1000000',
            ],

            'note' => [
                'required',
                'string',
            ],

            'documents' => [
                'sometimes',
                'array',
            ],

            'documents.*' => [
                'file',
                'max:20480',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'این فیلد الزامی است.',

            'end_date.after_or_equal' =>
                'تاریخ ختم نمی‌تواند قبل از تاریخ شروع باشد.',

            'office_employee_count.integer' =>
                'تعداد کارمندان باید عدد باشد.',

            'office_employee_count.min' =>
                'تعداد کارمندان نمی‌تواند منفی باشد.',

            'amniyat_mansobine_count.integer' =>
                'تعداد منسوبین امنیتی باید عدد باشد.',

            'amniyat_mansobine_count.min' =>
                'تعداد منسوبین امنیتی نمی‌تواند منفی باشد.',

            'meeting_supplier_phone.regex' =>
                'شماره تماس نامعتبر است.',

            'meeting_amniyat_responsable_phone.regex' =>
                'شماره تماس نامعتبر است.',

            'documents.*.max' =>
                'حجم هر فایل نباید بیشتر از ۲۰ میگابایت باشد.',
        ];
    }
}