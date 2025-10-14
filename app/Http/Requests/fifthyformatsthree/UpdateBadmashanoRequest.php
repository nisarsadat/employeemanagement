<?php

namespace App\Http\Requests\fifthyformatsthree;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBadmashanoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['sometimes','required','string'],
            'resource_id' => ['nullable','integer','exists:resources,id'],

            'nickname' => ['nullable','string'],
            'father_name' => ['nullable','string'],

            'main_province' => ['nullable','string'],
            'main_district' => ['nullable','string'],
            'main_village' => ['nullable','string'],

            'now_province' => ['nullable','string'],
            'now_zone' => ['nullable','string'],
            'now_district' => ['nullable','string'],
            'now_village' => ['nullable','string'],
            'now_specific_place' => ['nullable','string'],
            'now_street_number' => ['nullable','string'],
            'now_home_number' => ['nullable','string'],

            'old_job' => ['nullable','string'],
            'job' => ['nullable','string'],
            'friends_quantity' => ['nullable','string'],
            'supplier' => ['nullable','string'],
            'phone_number' => ['nullable','string'],

            'history_jorm' => ['nullable','string'],
            'jorm_type' => ['nullable','string'],
            'note' => ['nullable','string'],
        ];
    }
}
