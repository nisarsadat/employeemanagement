<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class ComplexResource extends JsonResource
{
    public function toArray($request): array
    {
        $model = $this->resource; // the underlying Eloquent model

        return [
            'id'                     => $model->id,

            'province'               => $model->province,
            'zone'                   => $model->zone,
            'district'               => $model->district,
            'village'                => $model->village,
            'specefic_place'         => $model->specefic_place,
            'street_number'          => $model->street_number,
            'home_number'            => $model->home_number,
            'picture' => $this->picture ? url($this->picture) : null,

            'complex_name'           => $model->complex_name,
            'activity_type'          => $model->activity_type,
            'activity_place'         => $model->activity_place,
            'supplier'               => $model->supplier,

            'enter_male_quantity'    => $model->enter_male_quantity,
            'outer_male_quantity'    => $model->outer_male_quantity,
            'enter_femal_quantity'   => $model->enter_femal_quantity,
            'outer_femal_quantity'   => $model->outer_femal_quantity,

            'responsible_name'       => $model->responsible_name,
            'responsible_job'        => $model->responsible_job,
            'responsible_f_name'     => $model->responsible_f_name,
            'responsible_phone'      => $model->responsible_phone,
            'responsible_country'    => $model->responsible_country,

            'guard_quantity'         => $model->guard_quantity,
            'coordinates'       => $model->coordinates,

           // Flattened from relation (match Fasad style)
            'resource_code'     => optional($model->resource)->code_number,
            'resource_phone'    => optional($model->resource)->phone_number,

            'resource_id'       => $model->resource_id,

            // Keep nested (match Fasad style)
            'resource'          => $this->whenLoaded('resource', function () use ($model) {
                return [
                    'id'   => optional($model->resource)->id,
                    'name' => optional($model->resource)->name,
                ];
            }),
            'note'                   => $model->note,
            'Province_addr'               => $model->Province_addr,

            'created_at'             => $model->created_at,
            'updated_at'             => $model->updated_at,
        ];
    }
}
