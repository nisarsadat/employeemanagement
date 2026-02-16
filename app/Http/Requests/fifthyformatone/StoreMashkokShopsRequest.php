<?php

namespace App\Http\Requests\fifthyformatone;

use Illuminate\Foundation\Http\FormRequest;

class StoreMashkokShopsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'province'       => ['nullable','string','max:255'],
            'zone'           => ['nullable','string','max:255'],
            'district'       => ['nullable','string','max:255'],
            'village'        => ['nullable','string','max:255'],
            'specific_place' => ['nullable','string','max:255'],
            'market_name'    => ['nullable','string','max:255'],
            'shop_number'    => ['nullable','string','max:255'],
            'coordinates'    => ['nullable','string','max:255'],
            'picture' => ['nullable','image'],

            'name'           => ['required','string','max:255'],
            'father_name'    => ['nullable','string','max:255'],
            'main_province'  => ['nullable','string','max:255'],
            'phone_numbers'  => ['nullable','string','max:255'],

            'shop_type'      => ['nullable','string','max:255'],
            'mashkok_type'   => ['nullable','string','max:255'],
            'Province_addr'             => ['nullable','string'],

            'note'           => ['nullable','string'],
            'tafseel'           => ['nullable','string'],
            'resource_id'    => ['nullable','integer','exists:resources,id'],
        ];
    }
}
