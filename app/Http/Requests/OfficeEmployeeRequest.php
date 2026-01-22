<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\UniqueCodeNumberAcrossStaff;

class OfficeEmployeeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('office_employee')?->id ?? $this->route('id');

        $base = [
            // Media
            'picture'                 => ['nullable','image'],

            // Identity
            'name'                    => ['required','string'],
            'father_name'             => ['nullable','string'],
            'grand_father_name'       => ['nullable','string'],
            'nick_name'               => ['nullable','string'],

            // Unique code number
            'code_number'             => [
                $this->isMethod('POST') ? 'required' : 'sometimes',
                'string',
                new UniqueCodeNumberAcrossStaff('office_employees', $id),
            ],

            // Rank/Levels & background
            'bast'                    => ['nullable','string'],
            'level'                   => ['nullable','string'],
            'history_jahad'           => ['nullable','string'],
            'dilgay_mesher'           => ['nullable','string'],
            'jahadi_mawaqaf'          => ['nullable','string'],
            'jahad_time_responsibility'=> ['nullable','string'],
            'tazkira'                 => ['nullable','string'],

            // Places
            'jahad_place'             => ['nullable','string'],
            'main_province'           => ['nullable','string'],
            'main_district'           => ['nullable','string'],
            'main_village'            => ['nullable','string'],

            'now_province'            => ['nullable','string'],
            'now_district'            => ['nullable','string'],
            'now_village'             => ['nullable','string'],
            'now_specific_place'      => ['nullable','string'],
            'now_street_number'       => ['nullable','string'],
            'now_home_number'         => ['nullable','string'],

            // Job & org
            'specific_job'            => ['nullable','string'],
            'qisem'                   => ['nullable','string'],
            'zone'                    => ['nullable','string'],
            'hawza'                   => ['nullable','string'],
            'makhafat'                => ['nullable','string'],
            'makafat'                 => ['nullable','string'],
            // Status & education
            'madani_halat'            => ['nullable','string'],
            'activity'                => ['nullable','string'],
            'relegy_study'            => ['nullable','string'],
            'new_study'               => ['nullable','string'],
            'faculty'                 => ['nullable','string'],
            'nizami'                  => ['nullable','string'],
            'job_type'                => ['nullable','string'],
            'gun_knowledge'           => ['nullable','string'],

            // Demographic
            'age'                     => ['nullable','integer','min:0'],
            'specific_skills'         => ['nullable','string'],
            'language'                => ['nullable','string'],
            'nation'                  => ['nullable','string'],

            // Contact & misc
            'phone_number'            => ['nullable','string','max:10', 'min:10'],
            'coordinates'             => ['nullable','string'],
            'join_date'               => ['nullable','date'],
            'left_date'               => ['nullable','date'],
            'note'                    => ['nullable','string'],
            'Province_addr' => ['nullable','string'],

            // Documents (handled elsewhere; allow multiple files)
            'documents'               => ['sometimes','array'],
            'documents.*'             => ['file','max:10240'],
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            foreach ($base as $key => $rules) {
                $base[$key] = is_array($rules) ? array_merge(['sometimes'], $rules) : ['sometimes', $rules];
            }
            // Keep 'name' required on full create only.
        }

        return $base;
    }
}
