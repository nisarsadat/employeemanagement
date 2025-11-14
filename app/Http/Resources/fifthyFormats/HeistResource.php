<?php

namespace App\Http\Resources\fifthyFormats;

use Illuminate\Http\Resources\Json\JsonResource;

class HeistResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'picture'         => $this->picture,
            'father_name'     => $this->father_name,
            'main_province'   => $this->main_province,
            'main_district'   => $this->main_district,
            'main_village'    => $this->main_village,
            'now_province'    => $this->now_province,
            'now_zone'        => $this->now_zone,
            'now_district'    => $this->now_district,
            'now_village'     => $this->now_village,
            'specific_place'  => $this->specific_place,
            'street_number'   => $this->street_number,
            'home_number'     => $this->home_number,
            'coordinates'     => $this->coordinates,
            'old_job'         => $this->old_job,
            'now_job'         => $this->now_job,
            'phone_number'    => $this->phone_number,
            'done_roberies'   => $this->done_roberies,
            'gun_type'        => $this->gun_type,
            'note'            => $this->note,
            'Province_addr'               => $this->Province_addr,

            'resource_id'     => $this->resource_id,
            'resource'        => $this->whenLoaded('resource', function () {
                return [
                    'id'            => $this->resource->id,
                    'name'          => $this->resource->name,
                    'code'          => $this->resource->code ?? null,
                    'phone_number'  => $this->resource->phone_number ?? null,
                ];
            }),

            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
