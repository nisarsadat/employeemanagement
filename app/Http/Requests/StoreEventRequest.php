<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resource_id' => 'nullable|exists:resources,id',
            'office_employee_id' => 'nullable|exists:office_employees,id',
            'event_type' => 'nullable|string',
            'event_specific_type' => 'nullable|string',
            'target_person' => 'nullable|string',
            'who_did' => 'nullable|string',
            'event_date' => 'nullable|string',
            'event_hour' => 'nullable|string',
            'zone' => 'nullable|string',
            'hawza' => 'nullable|string',
            'specific_place' => 'nullable|string',
            'jani_talafat' => 'nullable|string',
            'mali_talafat' => 'nullable|string',
            'event_office_employee_code' => 'nullable|exists:office_employees,id',
            'event_zone_employee_code' => 'nullable|exists:zone_employees,id',
            'tafseel' => 'nullable|string',
            'talks' => 'nullable|string',
            'notes' => 'nullable|string',
        ];
    }
}
