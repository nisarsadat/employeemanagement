<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuperMarketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // StoreSuperMarketRequest + UpdateSuperMarketRequest
    return [
        'supermarket_name'     => ['nullable','string'],
        'market_activity_type' => ['nullable','string'],
        'daily_clientele'      => ['nullable','integer'],
        'shops_quantity'       => ['nullable','integer'],
        'responsible_name'     => ['nullable','string'],
        'responsible_f_name'   => ['nullable','string'],
        'responsible_phone'    => ['nullable','string'],
        'province'             => ['nullable','string'],
        'zone'                 => ['nullable','string'],
        'district'             => ['nullable','string'],
        'village'              => ['nullable','string'],
        'specefic_place'       => ['nullable','string'],
        'coordinates'          => ['nullable','string'],
        'employee_quantity'    => ['nullable','integer'],
        'guard_quantity'       => ['nullable','integer'],
        'resource_id'          => ['nullable','integer','exists:resources,id'],
        'note'                 => ['nullable','string'],
            'Province_addr'         => ['nullable','string'],

    ];

    }
}
