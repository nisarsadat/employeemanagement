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
            'resource_id'           => 'sometimes|nullable|exists:resources,id',
            'office_employee_id'    => 'sometimes|nullable|exists:office_employees,id',
            'office_employee_akhistonky_id'    => 'sometimes|nullable|exists:office_employees,id',
            'event_type'            => 'sometimes|nullable|string',
            'event_specific_type'   => 'sometimes|nullable|string',
            'target_person'         => 'sometimes|nullable|string',
            'criminal'              => 'sometimes|nullable|string',
            'event_date'            => 'sometimes|nullable|string',
            'event_hour'            => 'sometimes|nullable|string',
            'event_zone'            => 'sometimes|nullable|string',
            'event_hawza'           => 'sometimes|nullable|string',
            'event_specific_place'  => 'sometimes|nullable|string',
            'talafat_jani'          => 'sometimes|nullable|string',
            'talafat_mali'          => 'sometimes|nullable|string',
            'injured'               => 'sometimes|nullable|string',
            'killed'                => 'sometimes|nullable|string',
            'trust_percentage'      => 'sometimes|nullable|string',
            'tafseel'               => 'sometimes|nullable|string',
            'talks'                 => 'sometimes|nullable|string',
            'coordinates'           => 'sometimes|nullable|string',
            'note'                  => 'sometimes|nullable|string',
            'docs_note'             => 'sometimes|nullable|string',
            'message_to_finance'    => 'sometimes|nullable|string',
            'finance_status'   => ['sometimes','nullable','string'],
            'finance_paid_at'   => ['sometimes','nullable','string'],
            'Province_addr'         => ['nullable','string'],

            'documents.*'           => 'sometimes|file',
        ];
    }
}
