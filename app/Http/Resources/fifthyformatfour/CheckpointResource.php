<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class CheckpointResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                 => $this->id,

            'checkpoint_name'    => $this->checkpoint_name,
            'employee_quantity'  => $this->employee_quantity,

            'organ'              => $this->organ,
            'create_reason'      => $this->create_reason,

            'officer_name'       => $this->officer_name,
            'officer_f_name'     => $this->officer_f_name,
            'officer_phone'      => $this->officer_phone,

            'gun_type'           => $this->gun_type,

            'province'           => $this->province,
            'zone'               => $this->zone,
            'district'           => $this->district,
            'village'            => $this->village,
            'specefic_place'     => $this->specefic_place,

            'coordinates'        => $this->coordinates,

            // Flattened Resource data
            'resource_code'      => $this->resource->code_number ?? null,
            'resource_phone'     => $this->resource->phone_number ?? null,

            'resource_id'        => $this->resource_id,
            'resource'           => $this->whenLoaded('resource', fn () => [
                'id'   => $this->resource->id,
                'name' => $this->resource->name ?? null,
            ]),

            'note'               => $this->note,
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
        ];
    }
}
