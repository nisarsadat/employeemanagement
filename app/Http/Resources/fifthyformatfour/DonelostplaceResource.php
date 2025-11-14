<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class DonelostplaceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                    => $this->id,

            'place_name'            => $this->place_name,

            'lost_person_name'      => $this->lost_person_name,
            'lost_person_f_name'    => $this->lost_person_f_name,
            'lost_person_phone'     => $this->lost_person_phone,
            'lost_person_job'       => $this->lost_person_job,

            'main_province'         => $this->main_province,
            'main_district'         => $this->main_district,
            'main_village'          => $this->main_village,

            'now_province'          => $this->now_province,
            'now_district'          => $this->now_district,
            'now_village'           => $this->now_village,

            'province'              => $this->province,
            'zone'                  => $this->zone,
            'district'              => $this->district,
            'village'               => $this->village,
            'specific_place'        => $this->specific_place,
            'coordinates'           => $this->coordinates,

            'lost_quantity'         => $this->lost_quantity,

            // Flatten from related Resource
            'resource_code'         => $this->resource->code_number ?? null,
            'resource_phone'        => $this->resource->phone_number ?? null,

            'resource_id'           => $this->resource_id,
            'resource'              => $this->whenLoaded('resource', fn () => [
                'id'   => $this->resource->id,
                'name' => $this->resource->name ?? null,
            ]),

            'note'                  => $this->note,
            'Province_addr'               => $this->Province_addr,

            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
        ];
    }
}
