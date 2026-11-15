<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMeetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'meeting_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'organ' => [
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

            'start_date' => [
                'nullable',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'total_time' => [
                'nullable',
                'string',
                'max:100',
            ],

            'participant' => [
                'nullable',
                'string',
            ],

            'boss_participant' => [
                'nullable',
                'string',
            ],

            'office_employee_count' => [
                'nullable',
                'integer',
                'min:0',
                'max:1000000',
            ],

            'leader_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'done_actions' => [
                'nullable',
                'string',
            ],

            'mashkok_situations' => [
                'nullable',
                'string',
            ],

            'problems' => [
                'nullable',
                'string',
            ],

            'meeting_supplier' => [
                'nullable',
                'string',
                'max:255',
            ],

            'meeting_supplier_phone' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[\p{L}\p{N}+\-\s()]+$/u',
            ],

            'meeting_amniyat_responsable' => [
                'nullable',
                'string',
                'max:255',
            ],

            'meeting_amniyat_responsable_phone' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[\p{L}\p{N}+\-\s()]+$/u',
            ],

            'amniyat_mansobine_count' => [
                'nullable',
                'integer',
                'min:0',
                'max:1000000',
            ],

            'note' => [
                'nullable',
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

            'removed_document_ids' => [
                'sometimes',
                'array',
            ],

            'removed_document_ids.*' => [
                'integer',
                'exists:meeting_documents,id',
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