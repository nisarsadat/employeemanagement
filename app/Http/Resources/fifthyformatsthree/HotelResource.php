<?php

namespace App\Http\Resources\fifthyformatsthree;

use Illuminate\Http\Resources\Json\JsonResource;

class HotelResource extends JsonResource
{
    public function toArray($request): array
    {
        $resource = $this->resource->getRelationValue('resource');

        return [
            'id'                 => $this->id,

            'hotel_name'         => $this->hotel_name,
            'type'               => $this->type,

            'responsible_name'   => $this->responsible_name,
            'responsible_f_name' => $this->responsible_f_name,
            'responsible_position'=> $this->responsible_position,
            'responsible_phone'  => $this->responsible_phone,

            'province'           => $this->province,
            'zone'               => $this->zone,
            'district'           => $this->district,
            'village'            => $this->village,
            'specific_place'     => $this->specific_place,
            'cordenate'          => $this->cordenate,

            'employee_quantity'  => $this->employee_quantity,
            'note'               => $this->note,

            'resource_id'        => $this->resource_id,

            // Flattened fields from Resource
            'resource_code'      => $resource?->code_number,
            'resource_phone'     => $resource?->phone_number,

            // Minimal embedded resource (when loaded)
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
