<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class MasjidResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                            => $this->id,

            'masjid_name'                   => $this->masjid_name,
            'masjid_type'                   => $this->masjid_type,

            'responsible_name'              => $this->responsible_name,
            'responsible_f_name'            => $this->responsible_f_name,
            'responsible_position'          => $this->responsible_position,
            'responsible_mazhab'            => $this->responsible_mazhab,
            'responsible_old_job'           => $this->responsible_old_job,
            'responsible_now_job'           => $this->responsible_now_job,
            'responsible_tarfdary'          => $this->responsible_tarfdary,

            'responsible_main_province'     => $this->responsible_main_province,
            'responsible_main_district'     => $this->responsible_main_district,
            'responsible_main_village'      => $this->responsible_main_village,
            'responsible_now_province'      => $this->responsible_now_province,
            'responsible_now_district'      => $this->responsible_now_district,
            'responsible_now_village'       => $this->responsible_now_village,

            'masjid_people_mazhab'          => $this->masjid_people_mazhab,
            'masjid_homes_quantity'         => $this->masjid_homes_quantity,

            'masjid_province'               => $this->masjid_province,
            'masjid_zone'                   => $this->masjid_zone,
            'masjid_district'               => $this->masjid_district,
            'masjid_village'                => $this->masjid_village,
            'masjid_specific_place'         => $this->masjid_specific_place,
            'masjid_street_number'          => $this->masjid_street_number,
            'masjid_coordinates'            => $this->masjid_coordinates,

            // Flatten from Resource
            'resource_code'                 => $this->resource->code_number ?? null,
            'resource_phone'                => $this->resource->phone_number ?? null,

            'resource_id'                   => $this->resource_id,
            'resource'                      => $this->whenLoaded('resource', fn () => [
                'id'   => $this->resource->id,
                'name' => $this->resource->name ?? null,
            ]),

            'note'                          => $this->note,
            'created_at'                    => $this->created_at,
            'updated_at'                    => $this->updated_at,
        ];
    }
}
