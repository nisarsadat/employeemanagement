<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class EconemyProjectResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                    => $this->id,
            'coordinates'           => $this->coordinates,

            'name'                  => $this->name,
            'type'                  => $this->type,

            'responsiable_name'     => $this->responsiable_name,
            'responsiable_f_name'   => $this->responsiable_f_name,
            'responsible_possition' => $this->responsible_possition,
            'responsible_phone'     => $this->responsible_phone,

            'province'              => $this->province,
            'zone'                  => $this->zone,
            'district'              => $this->district,
            'village'               => $this->village,
            'specefic_place'        => $this->specefic_place,

            'amnyatee_khala'        => $this->amnyatee_khala,
            'employee_quantity'     => $this->employee_quantity,
            'gaurd_quantity'        => $this->gaurd_quantity,

            // Flatten from Resource
            'resource_code'         => $this->resource->code_number ?? null,
            'resource_phone'        => $this->resource->phone_number ?? null,

            'resource_id'           => $this->resource_id,
            'resource'              => $this->whenLoaded('resource', fn () => [
                'id'   => $this->resource->id,
                'name' => $this->resource->name ?? null,
            ]),

            'note'                  => $this->note,
            'Province_addr'               => $this->Province_addr,

            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
        ];
    }
}
