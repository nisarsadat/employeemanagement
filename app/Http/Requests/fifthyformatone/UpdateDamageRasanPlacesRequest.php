<?php

namespace App\Http\Requests\fifthyformatone;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDamageRasanPlacesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // public function authorize(): bool
    // {
    //     return false;
    // }

   public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'province'        => ['nullable','string'],
            'zone'            => ['nullable','string'],
            'district'        => ['nullable','string'],
            'village'         => ['nullable','string'],
            'specific_place'  => ['nullable','string'],
            'specific_dot'    => ['nullable','string'],
            'coordinates'     => ['nullable','string'],
            'tahdid_type'     => ['nullable','string'],
            'percentage'      => ['nullable','string'],
            'damage_place'    => ['nullable','string'],
            'note'            => ['nullable','string'],
            'Province_addr'             => ['nullable','string'],
            'picture' => ['nullable','image'],

            'resource_id'     => ['nullable','integer','exists:resources,id'],
        ];
    }
}
