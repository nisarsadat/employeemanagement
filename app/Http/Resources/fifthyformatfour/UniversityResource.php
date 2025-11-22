<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class UniversityResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var \App\Models\fifthyformatfour\University $model */
        $model = $this->resource; // the underlying Eloquent model

        return [
            'id'                         => $model->id,

            'uni_name'                   => $model->uni_name,
            'uni_type'                   => $model->uni_type,

            'responsible_name'           => $model->responsible_name,
            'responsible_f_name'         => $model->responsible_f_name,
            'responsible_position'       => $model->responsible_position,
            'responsible_phone'          => $model->responsible_phone,

            'male_teachers_quantity'     => $model->male_teachers_quantity,
            'female_teachers_quantity'   => $model->female_teachers_quantity,
            'male_students_quantity'     => $model->male_students_quantity,
            'female_students_quantity'   => $model->female_students_quantity,

            'province'                   => $model->province,
            'zone'                       => $model->zone,
            'district'                   => $model->district,
            'village'                    => $model->village,
            'specific_place'             => $model->specific_place,
            'street_number'              => $model->street_number,
            'home_number'                => $model->home_number,

            'coordinates'                => $model->coordinates,
            'Province_addr'              => $model->Province_addr,

            'note'                       => $model->note,

            // 🔹 Flattened from relation (exactly like Mawad)
            'resource_code'              => optional($model->resource)->code_number,
            'resource_phone'             => optional($model->resource)->phone_number,

            'resource_id'                => $model->resource_id,

            // Keep nested minimal resource, same style as Mawad
            'resource'                   => $this->whenLoaded('resource', function () use ($model) {
                return [
                    'id'   => optional($model->resource)->id,
                    'name' => optional($model->resource)->name,
                ];
            }),

            'created_at'                 => $model->created_at,
            'updated_at'                 => $model->updated_at,
        ];
    }
}
