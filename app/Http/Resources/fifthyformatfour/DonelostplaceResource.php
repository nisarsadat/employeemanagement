<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class DonelostplaceResource extends JsonResource
{
    public function toArray($request): array
    {
        $model = $this->resource;

        return [
            'id'                 => $model->id,

            'place_name'         => $model->place_name,

            'lost_person_name'   => $model->lost_person_name,
            'lost_person_f_name' => $model->lost_person_f_name,
            'lost_person_phone'  => $model->lost_person_phone,
            'lost_person_job'    => $model->lost_person_job,

            'main_province'      => $model->main_province,
            'main_district'      => $model->main_district,
            'main_village'       => $model->main_village,

            'now_province'       => $model->now_province,
            'now_district'       => $model->now_district,
            'now_village'        => $model->now_village,

            'province'           => $model->province,
            'zone'               => $model->zone,
            'district'           => $model->district,
            'village'            => $model->village,
            'specific_place'     => $model->specific_place,
            'coordinates'        => $model->coordinates,

            'lost_quantity'      => $model->lost_quantity,

            // ✅ Flattened Resource fields (same style as Highway/Fasad/Mawad)
            'resource_code'      => optional($model->resource)->code_number,
            'resource_phone'     => optional($model->resource)->phone_number,

            'resource_id'        => $model->resource_id,

            // ✅ Minimal embedded resource (when loaded)
            'resource'           => $this->whenLoaded('resource', function () use ($model) {
                return [
                    'id'           => optional($model->resource)->id,
                    'name'         => optional($model->resource)->name,
                    'code_number'  => optional($model->resource)->code_number,
                    'phone_number' => optional($model->resource)->phone_number,
                ];
            }),

            'note'               => $model->note,
            'Province_addr'      => $model->Province_addr,

            'created_at'         => $model->created_at,
            'updated_at'         => $model->updated_at,
        ];
    }
}
