<?php

namespace App\Http\Requests\fifthyformatone;

use Illuminate\Foundation\Http\FormRequest;

class StoreDamageRasanPlacesRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'province'        => ['nullable','string','max:255'],
            'zone'            => ['nullable','string','max:255'],
            'district'        => ['nullable','string','max:255'],
            'village'         => ['nullable','string','max:255'],
            'specific_place'  => ['nullable','string','max:255'],
            'specific_dot'    => ['nullable','string','max:255'],
            'picture' => ['nullable','image'],
            'coordinates'     => ['nullable','string','max:255'],
            'tahdid_type'     => ['nullable','string','max:255'],
            'percentage'      => ['nullable','string','max:255'],
            'damage_place'    => ['nullable','string','max:255'],
            'note'            => ['nullable','string'],
            'Province_addr'             => ['nullable','string'],
            'resource_id'     => ['nullable','integer','exists:resources,id'],
        ];
    }
}
