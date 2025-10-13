<?php

namespace App\Http\Resources\fifthyformatstow;

use Illuminate\Http\Resources\Json\JsonResource;

class BankResource extends JsonResource
{
    public function toArray($request): array
    {
        $resource = $this->resource->getRelationValue('resource');

        return [
            'id'                   => $this->id,

            'bank_name'            => $this->bank_name,
            'bank_type'            => $this->bank_type,
            'bank_owner'           => $this->bank_owner,

            'responsible_name'     => $this->responsible_name,
            'responsible_f_name'   => $this->responsible_f_name,
            'responsible_position' => $this->responsible_position,
            'responsible_phone'    => $this->responsible_phone,

            'province'             => $this->province,
            'zone'                 => $this->zone,
            'district'             => $this->district,
            'village'              => $this->village,
            'specific_place'       => $this->specific_place,
            'coordinates'          => $this->coordinates,

            'guard_quantity'       => $this->guard_quantity,
            'note'                 => $this->note,

            'resource_id'          => $this->resource_id,
            // Flattened from related Resource (derived)
            'resource_code'        => $resource?->code_number,
            'resource_phone'       => $resource?->phone_number,

            'resource' => $this->whenLoaded('resource', function () use ($resource) {
                return $resource ? [
                    'id'           => $resource->id,
                    'name'         => $resource->name,
                    'code_number'  => $resource->code_number,
                    'phone_number' => $resource->phone_number,
                ] : null;
            }),

            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,
        ];
    }
}
