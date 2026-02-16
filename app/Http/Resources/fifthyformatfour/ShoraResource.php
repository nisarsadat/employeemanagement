<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class ShoraResource extends JsonResource
{
    public function toArray($request): array
    {
        $model = $this->resource; // the underlying Eloquent model

        return [
            'id'                     => $model->id,

            'shora_village'          => $model->shora_village,
            'note'                   => $model->note,

            'shora_name'             => $model->shora_name,
            'shora_goal'             => $model->shora_goal,
            'people_quantity'        => $model->people_quantity,
            'week_meeting_quantity'  => $model->week_meeting_quantity,
            'regester'               => $model->regester,
            'shora_meeting_place'    => $model->shora_meeting_place,
            'picture' => $this->picture ? url($this->picture) : null,

            'responsible_name'       => $model->responsible_name,
            'responsible_f_name'     => $model->responsible_f_name,
            'responsible_job'        => $model->responsible_job,
            'responsible_phone'      => $model->responsible_phone,

            'supplier_name'          => $model->supplier_name,
            'supplier_job'           => $model->supplier_job,
            'supplier_phone'         => $model->supplier_phone,

            'emarat_tarfdary'        => $model->emarat_tarfdary,

            'shora_province'         => $model->shora_province,
            'shora_zone'             => $model->shora_zone,
            'shora_district'         => $model->shora_district,
            'coordinates'         => $model->coordinates,

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
            'Province_addr'               => $model->Province_addr,

            'created_at'             => $model->created_at,
            'updated_at'             => $model->updated_at,
        ];
    }
}
