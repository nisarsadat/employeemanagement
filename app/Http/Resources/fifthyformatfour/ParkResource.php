<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class ParkResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                   => $this->id,

            'park_name'            => $this->park_name,
            'park_type'            => $this->park_type,
            'dayly_clientele'      => $this->dayly_clientele,

            'responsible_name'     => $this->responsible_name,
            'responsible_f_name'   => $this->responsible_f_name,
            'responsible_phone'    => $this->responsible_phone,
            'responsible_possition'=> $this->responsible_possition,

            'province'             => $this->province,
            'zone'                 => $this->zone,
            'village'              => $this->village,
            'specefic_place'       => $this->specefic_place,
            'coordinates'          => $this->coordinates,

            'employee_quantity'    => $this->employee_quantity,

            // Flatten from related Resource
            'resource_code'        => $this->resource->code_number ?? null,
            'resource_phone'       => $this->resource->phone_number ?? null,

            'resource_id'          => $this->resource_id,
            'resource'             => $this->whenLoaded('resource', fn () => [
                'id'   => $this->resource->id,
                'name' => $this->resource->name ?? null,
            ]),

            'notes'                => $this->notes,
            'Province_addr'               => $this->Province_addr,

            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,
        ];
    }
}
