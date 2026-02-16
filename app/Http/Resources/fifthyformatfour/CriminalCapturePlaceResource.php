<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class CriminalCapturePlaceResource extends JsonResource
{
    public function toArray($request): array
    {
        // ✅ Safe access to relation
        $resource = $this->getRelationValue('resource');

        return [
            'id'                 => $this->id,

            'name'               => $this->name,
            'father_name'        => $this->father_name,
            'job'                => $this->job,
            'picture' => $this->picture ? url($this->picture) : null,

            'jorm_type'          => $this->jorm_type,
            'criminals_quantity' => $this->criminals_quantity,
            'office'             => $this->office,

            'province'           => $this->province,
            'zone'               => $this->zone,
            'district'           => $this->district,
            'village'            => $this->village,
            'specefic_place'     => $this->specefic_place,

            'coordinates'        => $this->coordinates,
            'Province_addr'      => $this->Province_addr,

            // 🔗 Resource flatten
            'resource_id'            => $this->resource_id,
            'resource_code'          => $resource?->code_number,
            'resource_phone_number'  => $resource?->phone_number,

            // Minimal embedded resource for peek/detail views
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

            'note'              => $this->note,

            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}
