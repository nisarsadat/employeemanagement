<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class DeplomaticPlaceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                         => $this->id,

            'deplomatic_place_name'      => $this->deplomatic_place_name,
            'activity_type'              => $this->activity_type,
            'activity_place'             => $this->activity_place,

            'enter_male_quantity'        => $this->enter_male_quantity,
            'outer_male_quantity'        => $this->outer_male_quantity,
            'enter_female_quantity'      => $this->enter_female_quantity,
            'outer_female_quantity'      => $this->outer_female_quantity,

            'responsible_name'           => $this->responsible_name,
            'responsible_f_name'         => $this->responsible_f_name,
            'responsible_possition'      => $this->responsible_possition,
            'responsible_country'        => $this->responsible_country,
            'responsible_phone'          => $this->responsible_phone,

            'province'                   => $this->province,
            'zone'                       => $this->zone,
            'district'                   => $this->district,
            'village'                    => $this->village,
            'specefic_place'             => $this->specefic_place,
            'street_number'              => $this->street_number,
            'home_number'                => $this->home_number,

            'guard_quantity'             => $this->guard_quantity,
            'coordinates'                => $this->coordinates,

            // Flatten from related Resource
            'resource_code'              => $this->resource->code_number ?? null,
            'resource_phone'             => $this->resource->phone_number ?? null,

            'resource_id'                => $this->resource_id,
            'resource'                   => $this->whenLoaded('resource', fn () => [
                'id'   => $this->resource->id,
                'name' => $this->resource->name ?? null,
            ]),

            'note'                       => $this->note,
            'created_at'                 => $this->created_at,
            'updated_at'                 => $this->updated_at,
        ];
    }
}
