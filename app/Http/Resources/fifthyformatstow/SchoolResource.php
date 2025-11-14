<?php

namespace App\Http\Resources\fifthyformatstow;

use Illuminate\Http\Resources\Json\JsonResource;

class SchoolResource extends JsonResource
{
    public function toArray($request): array
    {
        $resource = $this->resource->getRelationValue('resource');

        return [
            'id'                        => $this->id,

            'school_name'               => $this->school_name,
            'school_type'               => $this->school_type,

            'principle_name'            => $this->principle_name,
            'principle_f_name'          => $this->principle_f_name,
            'principle_phone'           => $this->principle_phone,

            'male_teachers_quantity'    => $this->male_teachers_quantity,
            'female_teachers_quantity'  => $this->female_teachers_quantity,
            'male_students_quantity'    => $this->male_students_quantity,
            'female_students_quantity'  => $this->female_students_quantity,

            'province'                  => $this->province,
            'zone'                      => $this->zone,
            'district'                  => $this->district,
            'specific_place'            => $this->specific_place,
            'village'                   => $this->village,
            'street_number'             => $this->street_number,
            'coordinates'               => $this->coordinates,
            'Province_addr'               => $this->Province_addr,

            'note'                      => $this->note,

            'resource_id'               => $this->resource_id,
            // Flattened fields from related Resource (derived, not stored in schools table)
            'resource_code'             => $resource?->code_number,
            'resource_phone'            => $resource?->phone_number,

            // Minimal embedded resource when eager-loaded
            'resource' => $this->whenLoaded('resource', function () use ($resource) {
                return $resource ? [
                    'id'           => $resource->id,
                    'name'         => $resource->name,
                    'code_number'  => $resource->code_number,
                    'phone_number' => $resource->phone_number,
                ] : null;
            }),
            'created_at'                => $this->created_at,
            'updated_at'                => $this->updated_at,
        ];
    }
}
