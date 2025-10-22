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
            'resource_id'           => 'nullable|exists:resources,id',
            'office_employee_id'    => 'nullable|exists:office_employees,id',
            'event_type'            => 'nullable|string',
            'event_specific_type'   => 'nullable|string',
            'target_person'         => 'nullable|string',
            'criminal'              => 'nullable|string',
            'event_date'            => 'nullable|string',
            'event_hour'            => 'nullable|string',
            'event_zone'            => 'nullable|string',
            'event_hawza'           => 'nullable|string',
            'event_specific_place'  => 'nullable|string',
            'talafat_jani'          => 'nullable|string',
            'talafat_mali'          => 'nullable|string',
            'tafseel'               => 'nullable|string',
            'talks'                 => 'nullable|string',
            'coordinates'           => 'nullable|string',
            'note'                  => 'nullable|string',

            // documents handled as files (unchanged)
            'documents.*'           => 'nullable|file',
        ];
    }
}
