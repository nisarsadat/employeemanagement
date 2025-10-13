<?php

namespace App\Http\Resources\fifthyformatone;

use Illuminate\Http\Resources\Json\JsonResource;

class DamageRasanPlacesResource extends JsonResource
{
    public function toArray($request): array
    {
        // Safe accessor (avoid calling relation if not loaded)
        $res = $this->resource->getRelationValue('resource');

        return [
            'id'              => $this->id,
            'province'        => $this->province,
            'zone'            => $this->zone,
            'district'        => $this->district,
            'village'         => $this->village,
            'specific_place'  => $this->specific_place,
            'specific_dot'    => $this->specific_dot,
            'coordinates'     => $this->coordinates,
            'tahdid_type'     => $this->tahdid_type,
            'percentage'      => $this->percentage,
            'damage_place'    => $this->damage_place,
            'note'            => $this->note,
            'resource_id'     => $this->resource_id,

            // Derived (flattened) fields for frontend convenience
            'resource_code'   => $res->code_number  ?? null,
            'resource_phone'  => $res->phone_number ?? null,

            // Minimal embedded related resource
            'resource' => $this->whenLoaded('resource', function () use ($res) {
                return [
                    'id'           => $res->id ?? null,
                    'name'         => $res->name ?? null,
                    'code_number'  => $res->code_number ?? null,
                    'phone_number' => $res->phone_number ?? null,
                ];
            }),

            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
