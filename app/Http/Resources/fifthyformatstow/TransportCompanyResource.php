<?php

namespace App\Http\Resources\fifthyformatstow;

use Illuminate\Http\Resources\Json\JsonResource;

class TransportCompanyResource extends JsonResource
{
    // app/Http/Resources/fifthyformatstow/TransportCompanyResource.php
public function toArray($request): array
{
    // If eager-loaded, use it; otherwise fetch a minimal copy
    $resource = $this->relationLoaded('resource')
        ? $this->resource->getRelationValue('resource')
        : $this->resource()
            ->select('id','name','code_number','phone_number')
            ->first();

    return [
        'id'                         => $this->id,
        'transport_company_name'     => $this->transport_company_name,
        'type'                       => $this->type,
        'responsible_name'           => $this->responsible_name,
        'responsible_f_name'         => $this->responsible_f_name,
        'responsible_position'       => $this->responsible_position,
        'responsible_phone'          => $this->responsible_phone,
        'province'                   => $this->province,
        'zone'                       => $this->zone,
        'district'                   => $this->district,
        'village'                    => $this->village,
        'specific_place'             => $this->specific_place,
        'coordinate'                 => $this->coordinate,
        'ways'                       => $this->ways,
        'note'                       => $this->note,
        'Province_addr'               => $this->Province_addr,
        'picture' => $this->picture ? url($this->picture) : null,

        'resource_id'                => $this->resource_id,
        'resource_code'              => $resource?->code_number,
        'resource_phone'             => $resource?->phone_number,

        'resource' => $this->when(
            (bool) $resource,
            fn () => [
                'id'           => $resource->id,
                'name'         => $resource->name,
                'code_number'  => $resource->code_number,
                'phone_number' => $resource->phone_number,
            ]
        ),

        'created_at'                 => $this->created_at,
        'updated_at'                 => $this->updated_at,
    ];
}

}
