<?php

namespace App\Http\Resources\fifthyFormats;

use Illuminate\Http\Resources\Json\JsonResource;

class MahajeratResource extends JsonResource
{
    public function toArray($request): array
    {
        // Follow your CaptiveResource pattern
        $related = $this->resource->relationLoaded('resource')
            ? $this->resource->getRelation('resource')
            : null;

        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'father_name'       => $this->father_name,
            'scientific_level'  => $this->scientific_level,

            'main_province'     => $this->main_province,
            'main_district'     => $this->main_district,
            'main_village'      => $this->main_village,

            'now_province'      => $this->now_province,
            'now_zone'          => $this->now_zone,
            'now_district'      => $this->now_district,
            'now_vinage'        => $this->now_vinage,

            'specific_place'    => $this->specific_place,
            'street_number'     => $this->street_number,
            'home_number'       => $this->home_number,
            'cordinates'        => $this->cordinates,

            'country'           => $this->country,
            'male_amount'       => $this->male_amount,
            'female_amount'     => $this->female_amount,
            'phone_number'      => $this->phone_number,

            'note'              => $this->note,

            // relation id
            'resource_id'       => $this->resource_id,

            // flattened from related resource
            'resource_code'         => $related?->code_number,
            'resource_phone_number' => $related?->phone_number,

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

            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}
