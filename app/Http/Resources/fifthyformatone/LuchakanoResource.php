<?php

namespace App\Http\Resources\fifthyformatone;

use Illuminate\Http\Resources\Json\JsonResource;

class LuchakanoResource extends JsonResource
{
    public function toArray($request): array
    {
        // Safe accessor (only reads if relation is already loaded)
        $res = $this->resource->getRelationValue('resource');

        return [
            'id'                 => $this->id,

            'nickname'           => $this->nickname,
            'name'               => $this->name,
            'father_name'        => $this->father_name,

            'main_province'      => $this->main_province,
            'main_district'      => $this->main_district,
            'main_village'       => $this->main_village,

            'now_province'       => $this->now_province,
            'now_zone'           => $this->now_zone,
            'now_district'       => $this->now_district,
            'now_village'        => $this->now_village,
            'now_specific_place' => $this->now_specific_place,
            'now_street_number'  => $this->now_street_number,
            'now_home_number'    => $this->now_home_number,
            'Province_addr'               => $this->Province_addr,

            'old_job'            => $this->old_job,
            'job'                => $this->job,
            'gun_type'           => $this->gun_type,
            'phone_number'       => $this->phone_number,
            'history_jorm'       => $this->history_jorm,
            'note'               => $this->note,
            'coordinates'               => $this->coordinates,

            // FK
            'resource_id'        => $this->resource_id,

            // Flattened related fields
            'resource_code'      => $res->code_number  ?? null,
            'resource_phone'     => $res->phone_number ?? null,

            // Minimal embedded resource (only if eager-loaded)
            'resource' => $this->whenLoaded('resource', function () use ($res) {
                return [
                    'id'           => $res->id ?? null,
                    'name'         => $res->name ?? null,
                    'code_number'  => $res->code_number ?? null,
                    'phone_number' => $res->phone_number ?? null,
                ];
            }),

            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
        ];
    }
}
