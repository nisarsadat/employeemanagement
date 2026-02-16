<?php

namespace App\Http\Resources\fifthyformatone;

use Illuminate\Http\Resources\Json\JsonResource;

class MashkokShopsResource extends JsonResource
{
    public function toArray($request): array
    {
        // Safe relation accessor
        $res = $this->resource->getRelationValue('resource');

        return [
            'id'              => $this->id,
            'province'        => $this->province,
            'zone'            => $this->zone,
            'district'        => $this->district,
            'village'         => $this->village,
            'specific_place'  => $this->specific_place,
            'market_name'     => $this->market_name,
            'shop_number'     => $this->shop_number,
            'coordinates'     => $this->coordinates,
            'Province_addr'   => $this->Province_addr,
            'picture' => $this->picture ? url($this->picture) : null,

            'name'            => $this->name,
            'father_name'     => $this->father_name,
            'main_province'   => $this->main_province,
            'phone_numbers'   => $this->phone_numbers,

            'shop_type'       => $this->shop_type,
            'mashkok_type'    => $this->mashkok_type,

            'note'            => $this->note,
            'tafseel'            => $this->tafseel,
            'resource_id'     => $this->resource_id,

            // Flattened related fields
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
