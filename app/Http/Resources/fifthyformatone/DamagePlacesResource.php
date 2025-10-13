<?php

namespace App\Http\Resources\fifthyformatone;

use Illuminate\Http\Resources\Json\JsonResource;

class DamagePlacesResource extends JsonResource
{
    public function toArray($request): array
    {
        // Safe accessor (doesn't hit DB; only reads if relation is already loaded)
        $res = $this->resource->getRelationValue('resource');

        return [
            'id'               => $this->id,
            'province'         => $this->province,
            'zone'             => $this->zone,
            'district'         => $this->district,
            'village'          => $this->village,
            'specific_place'   => $this->specific_place,
            'specific_dot'     => $this->specific_dot,
            'coordinates'      => $this->coordinates,
            'tahdid_type'      => $this->tahdid_type,
            'percentage'       => $this->percentage,
            'tahdid_place'     => $this->tahdid_place,
            'amnyat_khala'     => $this->amnyat_khala,
            'cameesa_tafseel'  => $this->cameesa_tafseel,
            'guard_quantity'   => $this->guard_quantity,
            'masool_name'      => $this->masool_name,
            'masool_f_name'    => $this->masool_f_name,
            'masool_position'  => $this->masool_position,
            'masool_phone'     => $this->masool_phone,
            'note'             => $this->note,

            // FK
            'resource_id'      => $this->resource_id,

            // Flat derived fields expected by frontend
            'resource_code'    => $res->code_number  ?? null,
            'resource_phone'   => $res->phone_number ?? null,

            // Minimal embedded resource (only if eager-loaded)
            'resource' => $this->whenLoaded('resource', function () use ($res) {
                return [
                    'id'           => $res->id ?? null,
                    'name'         => $res->name ?? null,
                    'code_number'  => $res->code_number ?? null,
                    'phone_number' => $res->phone_number ?? null,
                ];
            }),

            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}
