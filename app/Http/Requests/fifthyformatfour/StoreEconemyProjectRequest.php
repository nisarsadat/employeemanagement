<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class StoreEconemyProjectRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'coordinates'           => ['nullable','string'],
            'name'                  => ['nullable','string'],
            'type'                  => ['nullable','string'],

            'responsiable_name'     => ['nullable','string'],
            'responsiable_f_name'   => ['nullable','string'],
            'responsible_possition' => ['nullable','string'],
            'responsible_phone'     => ['nullable','string'],

            'province'              => ['nullable','string'],
            'zone'                  => ['nullable','string'],
            'district'              => ['nullable','string'],
            'village'               => ['nullable','string'],
            'specefic_place'        => ['nullable','string'],

            'amnyatee_khala'        => ['nullable','string'],
            'employee_quantity'     => ['nullable','integer'],
            'gaurd_quantity'        => ['nullable','integer'],

            'resource_id'           => ['nullable','integer','exists:resources,id'],
            'note'                  => ['nullable','string'],
        ];
    }
}
