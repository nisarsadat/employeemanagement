<?php

namespace App\Http\Resources\fifthyformatsthree;

use Illuminate\Http\Resources\Json\JsonResource;

class TujaranResource extends JsonResource
{
    public function toArray($request): array
    {
        // Safe relation access
        $resource = $this->resource->getRelationValue('resource');

        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'father_name'  => $this->father_name,

            'main_province' => $this->main_province,
            'main_district' => $this->main_district,
            'main_village'  => $this->main_village,

            'now_province'       => $this->now_province,
            'now_zone'           => $this->now_zone,
            'now_district'       => $this->now_district,
            'now_village'        => $this->now_village,
            'now_specific_place' => $this->now_specific_place,
            'now_street_number'  => $this->now_street_number,
            'now_home_number'    => $this->now_home_number,

            'old_job' => $this->old_job,
            'now_job' => $this->now_job,
            'organ'   => $this->organ,

            'guard_guntity' => $this->guard_guntity,
            'phone_number'  => $this->phone_number,
            'people_type'   => $this->people_type,

            'note'        => $this->note,
            'resource_id' => $this->resource_id,

            // Flattened fields from Resource
            'resource_code'   => $resource?->code_number,
            'resource_phone'  => $resource?->phone_number,

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
