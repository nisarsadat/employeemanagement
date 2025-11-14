<?php

namespace App\Http\Resources\fifthyformatsthree;

use Illuminate\Http\Resources\Json\JsonResource;

class EmaratOfficeResource extends JsonResource
{
    public function toArray($request): array
    {
        $resource = $this->resource->getRelationValue('resource');

        return [
            'id'                => $this->id,
            'province'          => $this->province,
            'zone'              => $this->zone,
            'district'          => $this->district,
            'village'           => $this->village,
            'specific_place'    => $this->specific_place,
            'street_number'     => $this->street_number,
            'home_number'       => $this->home_number,
            'coordenates'       => $this->coordenates,
            'home_type'         => $this->home_type,
            'responsible_office'=> $this->responsible_office,
            'tahdid_mutawajeh'  => $this->tahdid_mutawajeh,
            'guard_quantity'    => $this->guard_quantity,
            'note'              => $this->note,
            'Province_addr'               => $this->Province_addr,

            'resource_id'       => $this->resource_id,
            'resource_code'     => $resource?->code_number,
            'resource_phone'    => $resource?->phone_number,

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
