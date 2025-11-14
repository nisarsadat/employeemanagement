<?php

namespace App\Http\Resources\fifthyformatsthree;

use Illuminate\Http\Resources\Json\JsonResource;

class BadmashanoResource extends JsonResource
{
    public function toArray($request): array
    {
        // Safe relation access
        $resource = $this->resource->getRelationValue('resource');

        return [
            'id'                => $this->id,

            'nickname'          => $this->nickname,
            'name'              => $this->name,
            'father_name'       => $this->father_name,

            'main_province'     => $this->main_province,
            'main_district'     => $this->main_district,
            'main_village'      => $this->main_village,

            'now_province'      => $this->now_province,
            'now_zone'          => $this->now_zone,
            'now_district'      => $this->now_district,
            'now_village'       => $this->now_village,
            'now_specific_place'=> $this->now_specific_place,
            'now_street_number' => $this->now_street_number,
            'now_home_number'   => $this->now_home_number,

            'old_job'           => $this->old_job,
            'job'               => $this->job,
            'friends_quantity'  => $this->friends_quantity,
            'supplier'          => $this->supplier,
            'phone_number'      => $this->phone_number,

            'history_jorm'      => $this->history_jorm,
            'jorm_type'         => $this->jorm_type,
            'note'              => $this->note,
            'Province_addr'               => $this->Province_addr,

            'resource_id'       => $this->resource_id,

            // Flattened fields from related Resource
            'resource_code'     => $resource?->code_number,
            'resource_phone'    => $resource?->phone_number,

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
