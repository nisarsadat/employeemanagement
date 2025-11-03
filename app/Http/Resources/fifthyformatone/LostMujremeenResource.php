<?php

namespace App\Http\Resources\fifthyformatone;

use Illuminate\Http\Resources\Json\JsonResource;

class LostMujremeenResource extends JsonResource
{
    public function toArray($request): array
    {
        $res = $this->resource->getRelationValue('resource');

        return [
            'id'          => $this->id,
            'picture'     => $this->picture,
            'nick_name'   => $this->nick_name,
            'name'        => $this->name,
            'father_name' => $this->father_name,

            'main_province' => $this->main_province,
            'main_district' => $this->main_district,
            'main_village'  => $this->main_village,

            'now_province'       => $this->now_province,
            'now_district'       => $this->now_district,
            'now_village'        => $this->now_village,
            'now_specific_place' => $this->now_specific_place,
            'now_street_number'  => $this->now_street_number,
            'now_home_number'    => $this->now_home_number,

            'province' => $this->province,
            'zone'     => $this->zone,
            'district' => $this->district,

            'old_job'      => $this->old_job,
            'jorm'         => $this->jorm,
            'history_jorm' => $this->history_jorm,
            'lost_reason'  => $this->lost_reason,

            'phone_number' => $this->phone_number,

            'note'        => $this->note,
            'resource_id' => $this->resource_id,

            // Flattened from Resource
            'resource_code'   => $res?->code_number,
            'resource_phone'  => $res?->phone_number,

            // Minimal embedded resource (when eager loaded)
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
