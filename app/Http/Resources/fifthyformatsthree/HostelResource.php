<?php

namespace App\Http\Resources\fifthyformatsthree;

use Illuminate\Http\Resources\Json\JsonResource;

class HostelResource extends JsonResource
{
    public function toArray($request): array
    {
        // Safe relation access
        $resource = $this->resource->getRelationValue('resource');

        return [
            'id'                 => $this->id,

            'hostel_name'        => $this->hostel_name,
            'hostel_type'        => $this->hostel_type,
            'people_quantity'    => $this->people_quantity,
            'people_type'        => $this->people_type,

            'responsible_name'     => $this->responsible_name,
            'responsible_f_name'   => $this->responsible_f_name,
            'responsible_position' => $this->responsible_position,
            'responsible_phone'    => $this->responsible_phone,
            'picture' => $this->picture ? url($this->picture) : null,

            'province'           => $this->province,
            'zone'               => $this->zone,
            'district'           => $this->district,
            'village'            => $this->village,
            'specific_place'     => $this->specific_place,
            'coordenates'        => $this->coordenates,
            'Province_addr'               => $this->Province_addr,

            'note'               => $this->note,

            'resource_id'        => $this->resource_id,

            // Flattened fields from related Resource
            'resource_code'      => $resource?->code_number,
            'resource_phone'     => $resource?->phone_number,

            // Minimal embedded resource (when eager-loaded)
            'resource' => $this->whenLoaded('resource', function () use ($resource) {
                return $resource ? [
                    'id'           => $resource->id,
                    'name'         => $resource->name,
                    'code_number'  => $resource->code_number,
                    'phone_number' => $resource->phone_number,
                ] : null;
            }),
        ];
    }
}
