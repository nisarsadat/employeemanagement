<?php

namespace App\Http\Requests\fifthyformatone;

use Illuminate\Foundation\Http\FormRequest;

class StoreLostMujremeenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // picture
            'picture' => ['nullable', 'image', 'max:5120'], // 5MB

            // identity
            'name'        => ['required', 'string', 'max:255'],
            'nickname'    => ['nullable', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],

            // main address
            'main_province' => ['nullable', 'string', 'max:255'],
            'main_district' => ['nullable', 'string', 'max:255'],
            'main_village'  => ['nullable', 'string', 'max:255'],

            // now address
            'now_province'       => ['nullable', 'string', 'max:255'],
            'now_district'       => ['nullable', 'string', 'max:255'],
            'now_village'        => ['nullable', 'string', 'max:255'],
            'now_specific_place' => ['nullable', 'string', 'max:255'],
            'now_street_number'  => ['nullable', 'string', 'max:255'],
            'now_home_number'    => ['nullable', 'string', 'max:255'],

            // general location
            'province' => ['nullable', 'string', 'max:255'],
            'zone'     => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],

            // extra
            'old_job'      => ['nullable', 'string', 'max:255'],
            'jorm'         => ['nullable', 'string', 'max:255'],
            'history_jorm' => ['nullable', 'string'],
            'lost_reason'  => ['nullable', 'string', 'max:255'],

            'Province_addr' => ['nullable', 'string', 'max:255'],
            'coordinates'   => ['nullable', 'string', 'max:255'],

            // resource
            'resource_id'   => ['nullable', 'integer', 'exists:resources,id'],
            'resource_code' => ['nullable', 'string', 'max:255'],
            'resource_phone'=> ['nullable', 'string', 'max:255'],

            // contact & note
            'phone_number' => ['nullable', 'string', 'max:50'],
            'note'         => ['nullable', 'string'],

            // ✅ docs
            '_newDocs'   => ['nullable', 'array'],
            '_newDocs.*' => [
                'file',
                'max:10240', // 10MB each
                'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,webp',
            ],
        ];
    }
}
