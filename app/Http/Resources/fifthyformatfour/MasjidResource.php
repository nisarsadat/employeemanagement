<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class MasjidResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var \App\Models\fifthyformatfour\Masjid $model */
        $model = $this->resource; // underlying Eloquent model

        return [
            'id'                            => $model->id,

            'masjid_name'                   => $model->masjid_name,
            'masjid_type'                   => $model->masjid_type,

            'responsible_name'              => $model->responsible_name,
            'responsible_f_name'            => $model->responsible_f_name,
            'responsible_position'          => $model->responsible_position,
            'responsible_mazhab'            => $model->responsible_mazhab,
            'responsible_old_job'           => $model->responsible_old_job,
            'responsible_now_job'           => $model->responsible_now_job,
            'responsible_tarfdary'          => $model->responsible_tarfdary,
            'picture' => $this->picture ? url($this->picture) : null,

            'responsible_main_province'     => $model->responsible_main_province,
            'responsible_main_district'     => $model->responsible_main_district,
            'responsible_main_village'      => $model->responsible_main_village,
            'responsible_now_province'      => $model->responsible_now_province,
            'responsible_now_district'      => $model->responsible_now_district,
            'responsible_now_village'       => $model->responsible_now_village,

            'masjid_people_mazhab'          => $model->masjid_people_mazhab,
            'masjid_homes_quantity'         => $model->masjid_homes_quantity,

            'masjid_province'               => $model->masjid_province,
            'masjid_zone'                   => $model->masjid_zone,
            'masjid_district'               => $model->masjid_district,
            'masjid_village'                => $model->masjid_village,
            'masjid_specific_place'         => $model->masjid_specific_place,
            'masjid_street_number'          => $model->masjid_street_number,
            'masjid_coordinates'            => $model->masjid_coordinates,

            // 🔹 Flattened from related Resource (same style as Mawad)
            'resource_code'                 => optional($model->resource)->code_number,
            'resource_phone'                => optional($model->resource)->phone_number,

            'resource_id'                   => $model->resource_id,

            // Minimal nested resource, same pattern
            'resource'                      => $this->whenLoaded('resource', function () use ($model) {
                return [
                    'id'   => optional($model->resource)->id,
                    'name' => optional($model->resource)->name,
                ];
            }),

            'note'                          => $model->note,
            'Province_addr'                 => $model->Province_addr,

            'created_at'                    => $model->created_at,
            'updated_at'                    => $model->updated_at,
        ];
    }
}
