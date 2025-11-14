<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class PumpResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'pump_name'           => $this->pump_name,
            'pump_type'           => $this->pump_type,
            'main_owner'          => $this->main_owner,

            'responsible_name'    => $this->responsible_name,
            'responsible_f_name'  => $this->responsible_f_name,
            'responsible_possition'=> $this->responsible_possition,
            'responsible_phone'   => $this->responsible_phone,

            'province'            => $this->province,
            'zone'                => $this->zone,
            'district'            => $this->district,
            'village'             => $this->village,
            'specefic_place'      => $this->specefic_place,

            'coordinates'         => $this->coordinates,
            'employee_quantity'   => $this->employee_quantity,
            'guard_quantity'      => $this->guard_quantity,

            // Flattened Resource data
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
