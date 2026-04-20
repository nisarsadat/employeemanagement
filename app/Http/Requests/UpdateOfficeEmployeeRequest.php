<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfficeEmployeeRequest extends FormRequest
{
    public function authorize(){ return true; }

    public function rules()
    {
        return [
            'picture'                 => ['sometimes','nullable','image'],

            'remove_picture' => ['sometimes', 'nullable', 'in:1'],
            'name'                    => ['sometimes','string'],
            'father_name'             => ['sometimes','nullable','string'],
            'grand_father_name'       => ['sometimes','nullable','string'],
            'nick_name'               => ['sometimes','nullable','string'],
            'code_number'             => ['sometimes','string'],

            'bast'                    => ['sometimes','nullable','string'],
            'level'                   => ['sometimes','nullable','string'],
            'history_jahad'           => ['sometimes','nullable','string'],
            'dilgay_mesher'           => ['sometimes','nullable','string'],
            'jahadi_mawaqaf'          => ['sometimes','nullable','string'],
            'jahad_time_responsibility'=> ['sometimes','nullable','string'],
            'tazkira'                 => ['sometimes','nullable','string'],

            'jahad_place'             => ['sometimes','nullable','string'],
            'main_province'           => ['sometimes','nullable','string'],
            'main_district'           => ['sometimes','nullable','string'],
            'main_village'            => ['sometimes','nullable','string'],

            'now_province'            => ['sometimes','nullable','string'],
            'now_district'            => ['sometimes','nullable','string'],
            'now_village'             => ['sometimes','nullable','string'],
            'now_specific_place'      => ['sometimes','nullable','string'],
            'now_street_number'       => ['sometimes','nullable','string'],
            'now_home_number'         => ['sometimes','nullable','string'],

            'specific_job'            => ['sometimes','nullable','string'],
            'qisem'                   => ['sometimes','nullable','string'],
            'zone'                    => ['sometimes','nullable','string'],
            'hawza'                   => ['sometimes','nullable','string'],
            'job_type'                => ['sometimes','nullable','string'],

            'madani_halat'            => ['sometimes','nullable','string'],
            'activity'                => ['sometimes','nullable','string'],
            'relegy_study'            => ['sometimes','nullable','string'],
            'new_study'               => ['sometimes','nullable','string'],
            'faculty'                 => ['sometimes','nullable','string'],
            'nizami'                  => ['sometimes','nullable','string'],
            'gun_knowledge'           => ['sometimes','nullable','string'],

            'age'                     => ['sometimes','nullable','integer','min:0','max:200'],
            'specific_skills'         => ['nullable','string'],

            'language'                => ['sometimes','nullable','string'],
            'nation'                  => ['sometimes','nullable','string'],

            'phone_number'            => ['sometimes','nullable','string','max:10' , 'min:10'],
            'coordinates'             => ['sometimes','nullable','string'],
            'join_date'               => ['sometimes','nullable','date'],
            'left_date'               => ['sometimes','nullable','date'],
            'note'                    => ['sometimes','nullable','string'],
            'Province_addr' => ['nullable','string'],

            'documents'               => ['sometimes','array'],
            'documents.*'             => ['file','max:10240'],
        ];
    }
}
