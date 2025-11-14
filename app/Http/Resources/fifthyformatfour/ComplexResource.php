<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class ComplexResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                     => $this->id,

            'province'               => $this->province,
            'zone'                   => $this->zone,
            'district'               => $this->district,
            'village'                => $this->village,
            'specefic_place'         => $this->specefic_place,
            'street_number'          => $this->street_number,
            'home_number'            => $this->home_number,

            'complex_name'           => $this->complex_name,
            'activity_type'          => $this->activity_type,
            'activity_place'         => $this->activity_place,
            'supplier'               => $this->supplier,

            'enter_male_quantity'    => $this->enter_male_quantity,
            'outer_male_quantity'    => $this->outer_male_quantity,
            'enter_femal_quantity'   => $this->enter_femal_quantity,
            'outer_femal_quantity'   => $this->outer_femal_quantity,

            'responsible_name'       => $this->responsible_name,
            'responsible_f_name'     => $this->responsible_f_name,
            'responsible_phone'      => $this->responsible_phone,
            'responsible_country'    => $this->responsible_country,

            'guard_quantity'         => $this->guard_quantity,

            // Flatten from Resource
            'resource_code'          => $this->resource->code_number ?? null,
            'resource_phone'         => $this->resource->phone_number ?? null,

            'resource_id'            => $this->resource_id,
            'resource'               => $this->whenLoaded('resource', fn () => [
                'id'   => $this->resource->id,
                'name' => $this->resource->name ?? null,
            ]),

            'note'                   => $this->note,
            'Province_addr'               => $this->Province_addr,

            'created_at'             => $this->created_at,
            'updated_at'             => $this->updated_at,
        ];
    }
}
