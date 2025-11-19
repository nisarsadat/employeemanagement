<?php

namespace App\Http\Resources\fifthyformatone;

use Illuminate\Http\Resources\Json\JsonResource;

class NonResponsablePeopleResource extends JsonResource
{
    public function toArray($request): array
    {
        $res = $this->whenLoaded('resource');

        return [
            'id'                   => $this->id,
            'nick_name'            => $this->nick_name,
            'name'                 => $this->name,
            'father_name'          => $this->father_name,

            'main_province'        => $this->main_province,
            'main_district'        => $this->main_district,
            'main_village'         => $this->main_village,

            'now_province'         => $this->now_province,
            'now_zone'             => $this->now_zone,
            'now_district'         => $this->now_district,
            'now_village'          => $this->now_village,
            'now_specific_place'   => $this->now_specific_place,
            'now_street_number'    => $this->now_street_number,
            'now_home_number'      => $this->now_home_number,
            'Province_addr'               => $this->Province_addr,

            'friends_quantity'     => $this->friends_quantity,
            'supplier'             => $this->supplier,
            'phone_number'         => $this->phone_number,
            'history_jorm'         => $this->history_jorm,
            'activity_place'       => $this->activity_place,
            'old_job'              => $this->old_job,
            'now_job'              => $this->now_job,
            'gun_type'             => $this->gun_type,
            'tanzim'               => $this->tanzim,
            'takhalof_type'        => $this->takhalof_type,
            'note'                 => $this->note,
            'resource_id'          => $this->resource_id,
            'coordinates'          => $this->coordinates,

            // Resource info (flattened)
            'resource_code'        => $res->code_number ?? null,
            'resource_phone'       => $res->phone_number ?? null,

            // Resource relation
            'resource' => $this->whenLoaded('resource', function () use ($res) {
                return [
                    'id'   => $res->id ?? null,
                    'name' => $res->name ?? null,
                ];
            }),

            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,
        ];
    }
}
