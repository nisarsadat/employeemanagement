<?php

namespace App\Http\Resources\fifthyformatstow;

use Illuminate\Http\Resources\Json\JsonResource;

class HosppitalResource extends JsonResource
{
    public function toArray($request): array
    {
        $resource = $this->resource->getRelationValue('resource');

        return [
            'id'                    => $this->id,

            'name'                  => $this->name,
            'specific_past'         => $this->specific_past,
            'hospita_type'          => $this->hospita_type,

            'responsible_name'      => $this->responsible_name,
            'responsible_position'  => $this->responsible_position,
            'responsible_phone'     => $this->responsible_phone,
            'responsible_f_name'    => $this->responsible_f_name,
            'picture' => $this->picture ? url($this->picture) : null,

            'male_quantity'         => $this->male_quantity,
            'female_quantity'       => $this->female_quantity,
            'guard_quantity'        => $this->guard_quantity,

            'province'              => $this->province,
            'zone'                  => $this->zone,
            'district'              => $this->district,
            'village'               => $this->village,
            'specific_place'        => $this->specific_place,
            'street_number'         => $this->street_number,
            'coordinates'           => $this->coordinates,
            'Province_addr'               => $this->Province_addr,

            'note'                  => $this->note,

            'resource_id'           => $this->resource_id,
            // Flattened derived fields from related Resource
            'resource_code'         => $resource?->code_number,
            'resource_phone'        => $resource?->phone_number,

            'resource' => $this->whenLoaded('resource', function () use ($resource) {
                return $resource ? [
                    'id'           => $resource->id,
                    'name'         => $resource->name,
                    'code_number'  => $resource->code_number,
                    'phone_number' => $resource->phone_number,
                ] : null;
            }),

            'created_at'            => $this->created_at,
            'updated_at'            => $this->updated_at,
        ];
    }
}
