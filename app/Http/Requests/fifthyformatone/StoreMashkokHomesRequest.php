<?php

namespace App\Http\Requests\fifthyformatone;

use Illuminate\Foundation\Http\FormRequest;

class StoreMashkokHomesRequest extends FormRequest
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
            'street_number'  => ['nullable','string','max:255'],
            'home_number'    => ['nullable','string','max:255'],
            'coordinates'    => ['nullable','string','max:255'],
            'home_type'      => ['nullable','string','max:255'],
            'shak_symbols'   => ['nullable','string','max:255'],
            'note'           => ['nullable','string'],
            'resource_id'    => ['nullable','integer','exists:resources,id'],
        ];
    }
}
