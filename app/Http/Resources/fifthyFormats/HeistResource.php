<?php

namespace App\Http\Resources\fifthyFormats;

use Illuminate\Http\Resources\Json\JsonResource;

class HeistResource extends JsonResource
{
    public function toArray($request): array
    {
        // ✅ Safely get the related resource if it's loaded
        $related = $this->relationLoaded('resource')
            ? $this->getRelation('resource')
            : null;

        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'picture'        => $this->picture,
            'father_name'    => $this->father_name,

            'main_province'  => $this->main_province,
            'main_district'  => $this->main_district,
            'main_village'   => $this->main_village,

            'now_province'   => $this->now_province,
            'now_zone'       => $this->now_zone,
            'now_district'   => $this->now_district,
            'now_village'    => $this->now_village,

            'specific_place' => $this->specific_place,
            'street_number'  => $this->street_number,
            'home_number'    => $this->home_number,
            'coordinates'    => $this->coordinates,

            'old_job'        => $this->old_job,
            'now_job'        => $this->now_job,

            'phone_number'   => $this->phone_number,
            'done_roberies'  => $this->done_roberies,
            'gun_type'       => $this->gun_type,
            'note'           => $this->note,
            'Province_addr'  => $this->Province_addr,

            // ✅ resource fields
            'resource_id'           => $this->resource_id,
            'resource_code'         => $related?->code_number,
            'resource_phone'        => $related?->phone_number,
            'resource_phone_number'        => $related?->phone_number,

            'resource' => $this->when(
                $related !== null,
                fn () => [
                    'id'           => $related->id,
                    'name'         => $related->name,
                    'code_number'  => $related->code_number,
                    'phone_number' => $related->phone_number,
                ]
            ),

            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
