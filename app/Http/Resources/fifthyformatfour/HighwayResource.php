<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class HighwayResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var \App\Models\fifthyformatfour\Highway $model */
        $model = $this->resource;

        return [
            'id'             => $model->id,
            'high_way_name'  => $model->high_way_name,

            'province'       => $model->province,
            'zone'           => $model->zone,
            'district'       => $model->district,
            'village'        => $model->village,
            'specific_place' => $model->specific_place,
            'coordinates'    => $model->coordinates,
            'picture' => $this->picture ? url($this->picture) : null,

            // ✅ Flattened Resource fields (Fasad/Mawad style)
            'resource_code'  => optional($model->resource)->code_number,
            'resource_phone' => optional($model->resource)->phone_number,

            'resource_id'    => $model->resource_id,

            // ✅ Minimal embedded resource (when loaded)
            'resource'       => $this->whenLoaded('resource', function () use ($model) {
                return [
                    'id'   => optional($model->resource)->id,
                    'name' => optional($model->resource)->name,
                    'code_number'  => optional($model->resource)->code_number,
                    'phone_number' => optional($model->resource)->phone_number,
                ];
            }),

            'note'           => $model->note,
            'Province_addr'  => $model->Province_addr,

            'created_at'     => $model->created_at,
            'updated_at'     => $model->updated_at,
        ];
    }
}
