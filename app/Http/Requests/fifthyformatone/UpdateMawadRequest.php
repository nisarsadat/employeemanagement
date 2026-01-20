<?php

namespace App\Http\Requests\fifthyformatone;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMawadRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'              => ['sometimes','required','string','max:255'],
            'mawad_type'        => ['nullable','string','max:255'],
            'nickname'          => ['nullable','string','max:255'],
            'father_name'       => ['nullable','string','max:255'],

            'main_province'     => ['nullable','string','max:255'],
            'main_district'     => ['nullable','string','max:255'],
            'main_village'      => ['nullable','string','max:255'],
            'picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],

            'now_province'      => ['nullable','string','max:255'],
            'now_district'      => ['nullable','string','max:255'],
            'now_village'       => ['nullable','string','max:255'],

            'province'          => ['nullable','string','max:255'],
            'zone'              => ['nullable','string','max:255'],
            'district'          => ['nullable','string','max:255'],
            'village'           => ['nullable','string','max:255'],

            'specific_place'    => ['nullable','string','max:255'],
            'coordinates'       => ['nullable','string','max:255'],
            'mawad_sales_phone' => ['nullable','string','max:100'],

            'history_form'      => ['nullable','string'],
            'supplier'          => ['nullable','string','max:255'],
            'note'              => ['nullable','string'],
            'Province_addr'             => ['nullable','string'],

            'resource_id'       => ['nullable','integer','exists:resources,id'],
        ];
    }
}
