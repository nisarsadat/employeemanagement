<?php

namespace App\Http\Resources\fifthyformatsthree;

use Illuminate\Http\Resources\Json\JsonResource;

class MujahedinoResource extends JsonResource
{
    public function toArray($request): array
    {
        $resource = $this->resource->getRelationValue('resource');

        return [
            'id' => $this->id,
            'nickname' => $this->nickname,
            'name' => $this->name,
            'father_name' => $this->father_name,

            'main_province' => $this->main_province,
            'main_district' => $this->main_district,
            'main_village'  => $this->main_village,

            'now_province' => $this->now_province,
            'now_zone' => $this->now_zone,
            'now_district' => $this->now_district,
            'now_village' => $this->now_village,
            'now_specific_place' => $this->now_specific_place,
            'now_street_number' => $this->now_street_number,
            'now_home_number' => $this->now_home_number,

            'duty_type' => $this->duty_type,
            'now_job' => $this->now_job,
            'organ' => $this->organ,
            'phone_number' => $this->phone_number,
            'note' => $this->note,
            'Province_addr'               => $this->Province_addr,

            'resource_id' => $this->resource_id,
            'resource_code' => $resource?->code_number,
            'resource_phone' => $resource?->phone_number,

            'resource' => $this->whenLoaded('resource', function () use ($resource) {
                return $resource ? [
                    'id' => $resource->id,
                    'name' => $resource->name,
                    'code_number' => $resource->code_number,
                    'phone_number' => $resource->phone_number,
                ] : null;
            }),
        ];
    }
}
