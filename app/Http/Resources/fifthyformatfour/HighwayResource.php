<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class HighwayResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'high_way_name'   => $this->high_way_name,
            'province'        => $this->province,
            'zone'            => $this->zone,
            'district'        => $this->district,
            'village'         => $this->village,
            'specific_place'  => $this->specific_place,
            'coordinates'     => $this->coordinates,

            // Flattened Resource fields
            'resource_code'   => $this->resource->code_number ?? null,
            'resource_phone'  => $this->resource->phone_number ?? null,

            'resource_id'     => $this->resource_id,
            'resource'        => $this->whenLoaded('resource', fn () => [
                'id'   => $this->resource->id,
                'name' => $this->resource->name ?? null,
            ]),

            'note'            => $this->note,
            'Province_addr'               => $this->Province_addr,

            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
