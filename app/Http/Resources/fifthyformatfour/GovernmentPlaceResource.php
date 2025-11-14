<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class GovernmentPlaceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                      => $this->id,
            'government_place_name'   => $this->government_place_name,
            'daily_clientele'         => $this->daily_clientele,

            'responsible_name'        => $this->responsible_name,
            'responsible_f_name'      => $this->responsible_f_name,
            'responsible_possition'   => $this->responsible_possition,
            'responsible_phone'       => $this->responsible_phone,

            'province'                => $this->province,
            'zone'                    => $this->zone,
            'district'                => $this->district,
            'village'                 => $this->village,
            'specefic_place'          => $this->specefic_place,
            'coordinates'             => $this->coordinates,

            'tafseel'                 => $this->tafseel,
            'amnyatee_khala'          => $this->amnyatee_khala,
            'guard_quantity'          => $this->guard_quantity,

            'resource_code'           => $this->resource->code_number ?? null,
            'resource_phone'          => $this->resource->phone_number ?? null,

            'resource_id'             => $this->resource_id,
            'resource'                => $this->whenLoaded('resource', fn () => [
                'id'   => $this->resource->id,
                'name' => $this->resource->name ?? null,
            ]),

            'note'                    => $this->note,
            'Province_addr'               => $this->Province_addr,

            'created_at'              => $this->created_at,
            'updated_at'              => $this->updated_at,
        ];
    }
}
