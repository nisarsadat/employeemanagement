<?php

namespace App\Http\Resources\fifthyformatone;

use Illuminate\Http\Resources\Json\JsonResource;

class OldSoldiersHomesResource extends JsonResource
{
    public function toArray($request): array
    {
        // $this->resource is the model instance
        // pull the *related* resource safely:
        $res = $this->resource->getRelationValue('resource'); // null if not loaded

        return [
            'id'              => $this->id,

            'province'        => $this->province,
            'zone'            => $this->zone,
            'district'        => $this->district,
            'village'         => $this->village,
            'specific_place'  => $this->specific_place,
            'street_number'   => $this->street_number,
            'home_number'     => $this->home_number,
            'coordinates'     => $this->coordinates,

            'name'            => $this->name,
            'father_name'     => $this->father_name,
            'main_province'   => $this->main_province,

            'old_job'         => $this->old_job,
            'now_activity'    => $this->now_activity,
            'note'            => $this->note,

            // derive from related resource (Fasad-style)
            'resource_code'   => $res->code_number  ?? null,
            'resource_phone'  => $res->phone_number ?? null,

            // keep FK for saving
            'resource_id'     => $this->resource_id,

            // if you also want to embed the related resource object
            'resource'        => $this->whenLoaded('resource', function () use ($res) {
                return [
                    'id'   => $res->id   ?? null,
                    'name' => $res->name ?? null,
                ];
            }),

            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
