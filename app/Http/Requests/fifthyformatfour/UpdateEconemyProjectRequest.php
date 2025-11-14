<?php

namespace App\Http\Requests\fifthyformatfour;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEconemyProjectRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'coordinates'           => ['sometimes','nullable','string'],
            'name'                  => ['sometimes','nullable','string'],
            'type'                  => ['sometimes','nullable','string'],

            'responsiable_name'     => ['sometimes','nullable','string'],
            'responsiable_f_name'   => ['sometimes','nullable','string'],
            'responsible_possition' => ['sometimes','nullable','string'],
            'responsible_phone'     => ['sometimes','nullable','string'],

            'province'              => ['sometimes','nullable','string'],
            'zone'                  => ['sometimes','nullable','string'],
            'district'              => ['sometimes','nullable','string'],
            'village'               => ['sometimes','nullable','string'],
            'specefic_place'        => ['sometimes','nullable','string'],

            'amnyatee_khala'        => ['sometimes','nullable','string'],
            'employee_quantity'     => ['sometimes','nullable','integer'],
            'gaurd_quantity'        => ['sometimes','nullable','integer'],

            'resource_id'           => ['sometimes','nullable','integer','exists:resources,id'],
            'note'                  => ['sometimes','nullable','string'],
            'Province_addr'         => ['sometimes','nullable','string'],

        ];
    }
}
