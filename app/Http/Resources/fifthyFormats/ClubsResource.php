<?php

namespace App\Http\Resources\fifthyFormats;

use Illuminate\Http\Resources\Json\JsonResource;

class ClubsResource extends JsonResource
{
    public function toArray($request): array
    {
        // same pattern you used in CaptiveResource
        $related = $this->resource->relationLoaded('resource')
            ? $this->resource->getRelation('resource')
            : null;

        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'club_name'        => $this->club_name,
            'club_type'        => $this->club_type,
            'father_name'      => $this->father_name,
            'position'         => $this->position,
            'phone_number'     => $this->phone_number,

            'province'         => $this->province,
            'zone'             => $this->zone,
            'notes'             => $this->notes,
            'district'         => $this->district,
            'specific_place'   => $this->specific_place,
            'cordinate'        => $this->cordinate,

            'am_students'      => $this->am_students,
            'pm_students'      => $this->pm_students,
            'picture' => $this->picture ? url($this->picture) : null,

            // relation id
            'resource_id'      => $this->resource_id,

            // flattened from related resource
            'resource_code'         => $related?->code_number,
            'resource_phone_number' => $related?->phone_number,
            'Province_addr'               => $this->Province_addr,

            // nested related
            'resource' => $this->when(
                $related !== null,
                fn () => [
                    'id'           => $related->id,
                    'name'         => $related->name,
                    'code_number'  => $related->code_number,
                    'phone_number' => $related->phone_number,
                ]
            ),

            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}
