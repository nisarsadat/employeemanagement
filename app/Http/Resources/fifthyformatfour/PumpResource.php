<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class PumpResource extends JsonResource
{
    public function toArray($request): array
    {
        $model = $this->resource; // the underlying Eloquent model

        return [
            'id'                  => $model->id,
            'pump_name'           => $model->pump_name,
            'pump_type'           => $model->pump_type,
            'main_owner'          => $model->main_owner,

            'responsible_name'    => $model->responsible_name,
            'responsible_f_name'  => $model->responsible_f_name,
            'responsible_possition'=> $model->responsible_possition,
            'responsible_phone'   => $model->responsible_phone,

            'province'            => $model->province,
            'zone'                => $model->zone,
            'district'            => $model->district,
            'village'             => $model->village,
            'specefic_place'      => $model->specefic_place,

            'coordinates'         => $model->coordinates,
            'employee_quantity'   => $model->employee_quantity,
            'guard_quantity'      => $model->guard_quantity,

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

            'note'                => $model->note,
            'Province_addr'               => $this->Province_addr,

            'created_at'          => $model->created_at,
            'updated_at'          => $model->updated_at,
        ];
    }
}
