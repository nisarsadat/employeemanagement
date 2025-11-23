<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class CheckpointResource extends JsonResource
{
    public function toArray($request): array
    {
        $model = $this->resource; // the underlying Eloquent model

        return [
            'id'                 => $model->id,

            'checkpoint_name'    => $model->checkpoint_name,
            'employee_quantity'  => $model->employee_quantity,

            'organ'              => $model->organ,
            'create_reason'      => $model->create_reason,

            'officer_name'       => $model->officer_name,
            'officer_f_name'     => $model->officer_f_name,
            'officer_phone'      => $model->officer_phone,

            'gun_type'           => $model->gun_type,

            'province'           => $model->province,
            'zone'               => $model->zone,
            'district'           => $model->district,
            'village'            => $model->village,
            'specefic_place'     => $model->specefic_place,

            'coordinates'        => $model->coordinates,

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

            'note'               => $model->note,
            'Province_addr'      => $model->Province_addr,
            'created_at'         => $model->created_at,
            'updated_at'         => $model->updated_at,
        ];
    }
}
