<?php

namespace App\Http\Resources\fifthyformats;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FasadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'nick_name'        => $this->nick_name,
            'father_name'      => $this->father_name,

            'main_province'    => $this->main_province,
            'main_district'    => $this->main_district,
            'main_village'     => $this->main_village,

            'now_province'     => $this->now_province,
            'now_zone'         => $this->now_zone,
            'now_district'     => $this->now_district,
            'now_village'      => $this->now_village,

            'specific_place'   => $this->specific_place,
            'street_number'    => $this->street_number,
            'home_number'      => $this->home_number,

            'jabha'            => $this->jabha,
            'supplier_name'    => $this->supplier_name,
            'responsibility'   => $this->responsibility,
            'activity_zone'    => $this->activity_zone,
            'friends_number'   => $this->friends_number,
            'phone_number'     => $this->phone_number,
            'bond_distance'    => $this->bond_distance,
            'last_band_reason' => $this->last_band_reason,
            'band_place'       => $this->band_place,
            'now_job'          => $this->now_job,

            'resource_code'    => $this->resource_code,
            'resource_phone'   => $this->resource_phone,
            'resource_id'      => $this->resource_id,
            'Province_addr'               => $this->Province_addr,
            'coordinates'     => $this->coordinates,

            // Minimal embedded resource (when loaded)
            'resource' => $this->whenLoaded('resource', function () {
                return [
                    'id'           => $this->resource->id,
                    'name'         => $this->resource->name,
                    'code_number'  => $this->resource->code_number,
                    'phone_number' => $this->resource->phone_number,
                ];
            }),
        ];
    }
}
