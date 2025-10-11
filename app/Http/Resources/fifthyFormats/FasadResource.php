<?php

namespace App\Http\Resources\fifthyFormats;

use Illuminate\Http\Resources\Json\JsonResource;

class FasadResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'picture'        => $this->picture,
            'nick_name'      => $this->nick_name,
            'father_name'    => $this->father_name,
            'main_province'  => $this->main_province,
            'main_district'  => $this->main_district,
            'main_village'   => $this->main_village,
            'now_province'   => $this->now_province,
            'now_zone'       => $this->now_zone,
            'now_district'   => $this->now_district,
            'now_village'    => $this->now_village,
            'specific_place' => $this->specific_place,
            'street_number'  => $this->street_number,
            'home_number'    => $this->home_number,
            'jabha'          => $this->jabha,
            'supplier_name'  => $this->supplier_name,
            'responsibility' => $this->responsibility,
            'activity_zone'  => $this->activity_zone,
            'friends_number' => $this->friends_number,
            'phone_number'   => $this->phone_number,
            'bond_distance'  => $this->bond_distance,
            'last_band_reason'=> $this->last_band_reason,
            'band_place'     => $this->band_place,
            'now_job'        => $this->now_job,
            'resource_code'  => $this->resource_code,
            'resource_phone' => $this->resource_phone,

            'resource_id'    => $this->resource_id,
            'resource'       => $this->whenLoaded('resource', fn () => [
                'id'   => $this->resource->id,
                'name' => $this->resource->name ?? null,
            ]),

            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
