<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class SuperMarketResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                   => $this->id,
            'supermarket_name'     => $this->supermarket_name,
            'market_activity_type' => $this->market_activity_type,
            'daily_clientele'      => $this->daily_clientele,
            'shops_quantity'       => $this->shops_quantity,
            'responsible_name'     => $this->responsible_name,
            'responsible_f_name'   => $this->responsible_f_name,
            'responsible_phone'    => $this->responsible_phone,
            'province'             => $this->province,
            'zone'                 => $this->zone,
            'district'             => $this->district,
            'village'              => $this->village,
            'specefic_place'       => $this->specefic_place,
            'coordinates'          => $this->coordinates,
            'employee_quantity'    => $this->employee_quantity,
            'guard_quantity'       => $this->guard_quantity,

            // Resource flatten
            'resource_code'        => $this->resource->code_number ?? null,
            'resource_phone'       => $this->resource->phone_number ?? null,
            'resource_id'          => $this->resource_id,
            'resource'             => $this->whenLoaded('resource', fn () => [
                'id'   => $this->resource->id,
                'name' => $this->resource->name ?? null,
            ]),

            'note'                 => $this->note,
            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,
        ];
    }
}
