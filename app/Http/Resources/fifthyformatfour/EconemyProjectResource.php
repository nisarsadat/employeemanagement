<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class EconemyProjectResource extends JsonResource
{
    public function toArray($request): array
    {
        // ✅ Safe access to relation (doesn't hit DB again if not loaded)
        $resource = $this->getRelationValue('resource');

        return [
            'id'              => $this->id,
            'coordinates'     => $this->coordinates,

            // Base info
            'name'            => $this->name,
            'type'            => $this->type,

            // Responsible person
            'responsiable_name'     => $this->responsiable_name,
            'responsiable_f_name'   => $this->responsiable_f_name,
            'responsible_possition' => $this->responsible_possition,
            'responsible_phone'     => $this->responsible_phone,
            'picture' => $this->picture ? url($this->picture) : null,

            // Address
            'province'        => $this->province,
            'zone'            => $this->zone,
            'district'        => $this->district,
            'village'         => $this->village,
            'specefic_place'  => $this->specefic_place,

            // Stats
            'amnyatee_khala'    => $this->amnyatee_khala,
            'employee_quantity' => $this->employee_quantity,
            'gaurd_quantity'    => $this->gaurd_quantity,

            // 🔗 Resource linkage (flattened)
            'resource_id'          => $this->resource_id,
            'resource_code'        => $resource?->code_number,
            'resource_phone_number'=> $resource?->phone_number,

            // Minimal embedded resource (for detail / peek views)
            'resource' => $this->whenLoaded('resource', function () use ($resource) {
                if (! $resource) {
                    return null;
                }

                return [
                    'id'           => $resource->id,
                    'name'         => $resource->name ?? null,
                    'code_number'  => $resource->code_number ?? null,
                    'phone_number' => $resource->phone_number ?? null,
                ];
            }),

            'note'           => $this->note,
            'Province_addr'  => $this->Province_addr,

            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
