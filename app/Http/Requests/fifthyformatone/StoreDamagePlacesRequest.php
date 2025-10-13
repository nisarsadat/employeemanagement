<?php

namespace App\Http\Requests\fifthyformatone;

use Illuminate\Foundation\Http\FormRequest;

class StoreDamagePlacesRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'province'         => ['nullable','string','max:255'],
            'zone'             => ['nullable','string','max:255'],
            'district'         => ['nullable','string','max:255'],
            'village'          => ['nullable','string','max:255'],
            'specific_place'   => ['nullable','string','max:255'],
            'specific_dot'     => ['nullable','string','max:255'],
            'coordinates'      => ['nullable','string','max:255'],
            'tahdid_type'      => ['nullable','string','max:255'],
            'percentage'       => ['nullable','string','max:255'],
            'tahdid_place'     => ['nullable','string','max:255'],
            'amnyat_khala'     => ['nullable','string','max:255'],
            'cameesa_tafseel'  => ['nullable','string'],
            'guard_quantity'   => ['nullable','string','max:255'],
            'masool_name'      => ['nullable','string','max:255'],
            'masool_f_name'    => ['nullable','string','max:255'],
            'masool_position'  => ['nullable','string','max:255'],
            'masool_phone'     => ['nullable','string','max:255'],
            'note'             => ['nullable','string'],
            'resource_id'      => ['nullable','integer','exists:resources,id'],
        ];
    }
}
