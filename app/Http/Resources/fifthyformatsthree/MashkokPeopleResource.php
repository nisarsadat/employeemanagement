<?php

namespace App\Http\Resources\fifthyformatsthree;

use Illuminate\Http\Resources\Json\JsonResource;

class MashkokPeopleResource extends JsonResource
{
    public function toArray($request): array
    {
        $res = $this->resource->getRelationValue('resource');

        // ✅ make picture usable in SPA (absolute URL if picture exists)
        $pictureUrl = $this->picture
            ? url($this->picture)   // e.g. http://your-app.test/storage/...
            : null;

        return [
            'id'                 => $this->id,
            'picture'            => $pictureUrl,
            'name'               => $this->name,
            'father_name'        => $this->father_name,

            'main_province'      => $this->main_province,
            'main_district'      => $this->main_district,
            'main_village'       => $this->main_village,

            'now_province'       => $this->now_province,
            'now_zone'           => $this->now_zone,
            'now_districts'      => $this->now_districts,
            'now_village'        => $this->now_village,
            'now_specific_place' => $this->now_specific_place,
            'now_street_number'  => $this->now_street_number,
            'now_home_number'    => $this->now_home_number,
            'coordinates'        => $this->coordinates,
            'mashkokyat_type'        => $this->mashkokyat_type,

            'old_job'            => $this->old_job,
            'now_job'            => $this->now_job,
            'phone_number'       => $this->phone_number,
            'mushkokyat_points'  => $this->mushkokyat_points,
            'Province_addr'      => $this->Province_addr,

            'note'               => $this->note,
            'tafseel'               => $this->tafseel,
            'resource_id'        => $this->resource_id,

            // Flattened from Resource
            'resource_code'      => $res?->code_number,
            'resource_phone'     => $res?->phone_number,

            // Minimal embedded resource
            'resource' => $this->whenLoaded('resource', function () use ($res) {
                return $res ? [
                    'id'           => $res->id,
                    'name'         => $res->name,
                    'code_number'  => $res->code_number,
                    'phone_number' => $res->phone_number,
                ] : null;
            }),
        ];
    }
}
