<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'resource_id' => 'sometimes|exists:resources,id',
            'office_employee_id' => 'sometimes|exists:office_employees,id',
            'event_type' => 'sometimes|string',
            'event_specific_type' => 'sometimes|string',
            'target_person' => 'sometimes|string',
            'who_did' => 'sometimes|string',
            'event_date' => 'sometimes|string',
            'event_hour' => 'sometimes|string',
            'zone' => 'sometimes|string',
            'hawza' => 'sometimes|string',
            'specific_place' => 'sometimes|string',
            'jani_talafat' => 'sometimes|string',
            'mali_talafat' => 'sometimes|string',
            'event_office_employee_code' => 'sometimes|exists:office_employees,id',
            'event_zone_employee_code' => 'sometimes|exists:zone_employees,id',
            'tafseel' => 'sometimes|string',
            'talks' => 'sometimes|string',
            'notes' => 'sometimes|string',
        ];
    }
}
