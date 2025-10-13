<?php

namespace App\Http\Resources\fifthyformatstow;

use Illuminate\Http\Resources\Json\JsonResource;

class ParkingResource extends JsonResource
{
    public function toArray($request): array
    {
        $resource = $this->resource->getRelationValue('resource');

        return [
            'id'                          => $this->id,

            'parking_name'                => $this->parking_name,
            'parking_type'                => $this->parking_type,
            'parking_type_pe'             => $this->parking_type_pe,

            'responsible_name'            => $this->responsible_name,
            'responsible_f_name'          => $this->responsible_f_name,
            'responsible_position'        => $this->responsible_position,
            'responsible_phone'           => $this->responsible_phone,

            'province'                    => $this->province,
            'zone'                        => $this->zone,
            'district'                    => $this->district,
            'village'                     => $this->village,
            'specific_place'              => $this->specific_place,
            'coordinates'                 => $this->coordinates,

            'daily_cars_parking_quantity' => $this->daily_cars_parking_quantity,
            'note'                        => $this->note,

            'resource_id'                 => $this->resource_id,
            'resource_code'               => $resource?->code_number,
            'resource_phone'              => $resource?->phone_number,

            'resource' => $this->whenLoaded('resource', function () use ($resource) {
                return $resource ? [
                    'id'           => $resource->id,
                    'name'         => $resource->name,
                    'code_number'  => $resource->code_number,
                    'phone_number' => $resource->phone_number,
                ] : null;
            }),

            'created_at'                  => $this->created_at,
            'updated_at'                  => $this->updated_at,
        ];
    }
}
