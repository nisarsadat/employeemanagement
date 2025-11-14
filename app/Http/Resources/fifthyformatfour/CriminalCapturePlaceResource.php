<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class CriminalCapturePlaceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,

            'name'                => $this->name,
            'father_name'         => $this->father_name,
            'job'                 => $this->job,

            'jorm_type'           => $this->joom_type,
            'criminals_quantity'  => $this->criminals_quantity,
            'office'              => $this->office,

            'province'            => $this->province,
            'zone'                => $this->zone,
            'district'            => $this->district,
            'village'             => $this->village,
            'specefic_place'      => $this->specefic_place,

            'coordinates'         => $this->coordinates,

            // Flatten from Resource
            'resource_code'       => $this->resource->code_number ?? null,
            'resource_phone'      => $this->resource->phone_number ?? null,

            'resource_id'         => $this->resource_id,
            'resource'            => $this->whenLoaded('resource', fn () => [
                'id'   => $this->resource->id,
                'name' => $this->resource->name ?? null,
            ]),

            'note'                => $this->note,
            'Province_addr'               => $this->Province_addr,

            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
        ];
    }
}
