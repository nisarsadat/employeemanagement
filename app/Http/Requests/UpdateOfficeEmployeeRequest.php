<?php

// app/Http/Requests/UpdateOfficeEmployeeRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOfficeEmployeeRequest extends FormRequest
{
    public function authorize(){ return true; }
    public function rules()
    {
        // all fields optional for PATCH-like behavior
        return [
            'picture'        => ['nullable','image','max:2048'],
            'name'           => ['sometimes','string','max:255'],
            'father_name'    => ['sometimes','nullable','string','max:255'],
            'nickname'       => ['sometimes','nullable','string','max:255'],
            'main_province'  => ['sometimes','nullable','string','max:255'],
            'main_district'  => ['sometimes','nullable','string','max:255'],
            'main_village'   => ['sometimes','nullable','string','max:255'],
            'now_province'   => ['sometimes','nullable','string','max:255'],
            'now_district'   => ['sometimes','nullable','string','max:255'],
            'now_village'    => ['sometimes','nullable','string','max:255'],
            'releg_study'    => ['sometimes','nullable','string','max:255'],
            'new_study'      => ['sometimes','nullable','string','max:255'],
            'phone_number'   => ['sometimes','nullable','string','max:50'],
            'tazkira'        => ['sometimes','nullable','string','max:100'],
            'apply_date'     => ['sometimes','nullable','date'],
            'join_date'      => ['sometimes','nullable','date'],
            'left_date'      => ['sometimes','nullable','date'],
            'notes'          => ['sometimes','nullable','string'],
            'old_picture'    => ['sometimes','nullable','string'],
        ];
    }
}
