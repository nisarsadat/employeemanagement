<?php

namespace App\Http\Requests;

use App\Models\KhayratReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreKhayratReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resource_id' => ['required', 'integer', 'exists:resources,id'],
            'office_employee_id' => ['required', 'integer', 'exists:office_employees,id'],
            'zone' => ['nullable', 'string', 'max:255'],
            'hawza' => ['nullable', 'string', 'max:255'],
            'specefic_place' => ['nullable', 'string', 'max:255'],
            'saved_date' => ['required', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $resourceId = (int) $this->input('resource_id');
            $savedDate  = $this->input('saved_date');

            if (!$resourceId || !$savedDate) {
                return;
            }

            $d = \Carbon\Carbon::parse($savedDate);
            $start = $d->copy()->startOfMonth()->toDateString();
            $end   = $d->copy()->endOfMonth()->toDateString();

            $exists = KhayratReport::query()
                ->where('resource_id', $resourceId)
                ->whereDate('saved_date', '>=', $start)
                ->whereDate('saved_date', '<=', $end)
                ->exists();

            if ($exists) {
                $validator->errors()->add(
                    'saved_date',
                    'برای این Resource در همین ماه قبلاً راپور ثبت شده است. (One report per month)'
                );
            }
        });
    }
}
