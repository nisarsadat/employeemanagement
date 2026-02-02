<?php

namespace App\Http\Requests\fifthyformats;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJawasisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'picture' => ['nullable', 'file', 'image', 'max:5120'],

            'name' => ['nullable', 'string', 'max:255'],
            'nick_name' => ['nullable', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],

            'main_province' => ['nullable', 'string', 'max:255'],
            'main_district' => ['nullable', 'string', 'max:255'],
            'main_village' => ['nullable', 'string', 'max:255'],

            'now_province' => ['nullable', 'string', 'max:255'],
            'now_zone' => ['nullable', 'string', 'max:255'],
            'now_district' => ['nullable', 'string', 'max:255'],
            'now_village' => ['nullable', 'string', 'max:255'],
            'now_specific_place' => ['nullable', 'string', 'max:255'],
            'now_street_number' => ['nullable', 'string', 'max:255'],
            'now_home_number' => ['nullable', 'string', 'max:255'],

            'coordinates' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],

            'responsiability' => ['nullable', 'string'],
            'done_activities' => ['nullable', 'string'],
            'activity_place' => ['nullable', 'string', 'max:255'],

            'count_of_communication' => ['nullable', 'integer', 'min:0'],
            'phone_number' => ['nullable', 'string', 'max:255'],

            'old_job' => ['nullable', 'string', 'max:255'],
            'now_job' => ['nullable', 'string', 'max:255'],

            'resource_id' => ['nullable', 'integer', 'exists:resources,id'],
            'notes' => ['nullable', 'string'],

            // docs
            'documents' => ['nullable', 'array'],
            'documents.*' => ['file', 'max:10240'],

            'removed_document_ids' => ['nullable', 'array'],
            'removed_document_ids.*' => ['integer'],
        ];
    }
}
