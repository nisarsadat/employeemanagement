<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class UniversityResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                         => $this->id,

            'uni_name'                   => $this->uni_name,
            'uni_type'                   => $this->uni_type,

            'responsible_name'           => $this->responsible_name,
            'responsible_f_name'         => $this->responsible_f_name,
            'responsible_position'       => $this->responsible_position,
            'responsible_phone'          => $this->responsible_phone,

            'male_teachers_quantity'     => $this->male_teachers_quantity,
            'female_teachers_quantity'   => $this->female_teachers_quantity,
            'male_students_quantity'     => $this->male_students_quantity,
            'female_students_quantity'   => $this->female_students_quantity,

            'province'                   => $this->province,
            'zone'                       => $this->zone,
            'district'                   => $this->district,
            'village'                    => $this->village,
            'specific_place'             => $this->specific_place,
            'street_number'              => $this->street_number,
            'home_number'                => $this->home_number,

            'coordinates'                => $this->coordinates,

            // Flatten from related Resource
            'resource_code'              => $this->resource->code_number ?? null,
            'resource_phone'             => $this->resource->phone_number ?? null,

            'resource_id'                => $this->resource_id,
            'resource'                   => $this->whenLoaded('resource', fn () => [
                'id'   => $this->resource->id,
                'name' => $this->resource->name ?? null,
            ]),

            'note'                       => $this->note,
            'Province_addr'               => $this->Province_addr,

            'created_at'                 => $this->created_at,
            'updated_at'                 => $this->updated_at,
        ];
    }
}
