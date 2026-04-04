<?php

namespace App\Http\Requests;

use App\Models\KhayratReport;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateKhayratReportRequest extends FormRequest
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
            $savedDate = $this->input('saved_date');

            if (!$resourceId || !$savedDate) {
                return;
            }

            $routeModel = $this->route('khayratReport') ?? $this->route('khayrat_report');
            $currentId = $routeModel?->id ?? $routeModel?->getKey();

            $date = Carbon::parse($savedDate);
            $startOfMonth = $date->copy()->startOfMonth()->toDateString();
            $endOfMonth = $date->copy()->endOfMonth()->toDateString();

            $exists = KhayratReport::query()
                ->where('resource_id', $resourceId)
                ->whereDate('saved_date', '>=', $startOfMonth)
                ->whereDate('saved_date', '<=', $endOfMonth)
                ->when($currentId, fn ($query) => $query->where('id', '!=', $currentId))
                ->exists();

            if ($exists) {
                $validator->errors()->add(
                    'saved_date',
                    'د دې منبع لپاره په همدې میاشت کې مخکې راپور ثبت شوی دی.'
                );
            }
        });
    }
}