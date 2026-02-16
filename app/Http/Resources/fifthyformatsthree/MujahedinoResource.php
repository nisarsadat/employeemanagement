<?php

namespace App\Http\Resources\fifthyformatsthree;

use Illuminate\Http\Resources\Json\JsonResource;

class MujahedinoResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var \App\Models\fifthyformatsthree\Mujahedino $model */
        $model = $this->resource;

        // Safe access to loaded relation
        $resource = $model->getRelationValue('resource');

        return [
            'id' => $model->id,

            'nickname' => $model->nickname,
            'name' => $model->name,
            'father_name' => $model->father_name,

            'main_province' => $model->main_province,
            'main_district' => $model->main_district,
            'main_village'  => $model->main_village,
            'picture' => $this->picture ? url($this->picture) : null,

            'now_province' => $model->now_province,
            'now_zone' => $model->now_zone,
            'now_district' => $model->now_district,
            'now_village' => $model->now_village,
            'now_specific_place' => $model->now_specific_place,
            'now_street_number' => $model->now_street_number,
            'now_home_number' => $model->now_home_number,

            'duty_type' => $model->duty_type,
            'now_job' => $model->now_job,
            'organ' => $model->organ,
            'friends_quantity' => $model->friends_quantity,
            'phone_number' => $model->phone_number,
            'note' => $model->note,
            'coordinates' => $model->coordinates,
            'Province_addr' => $model->Province_addr,

            // Linkage
            'resource_id' => $model->resource_id,
            'resource_code' => $resource?->code_number,
            'resource_phone' => $resource?->phone_number,

            // Minimal embedded resource
            'resource' => $this->whenLoaded('resource', function () use ($resource) {
                return $resource ? [
                    'id' => $resource->id,
                    'name' => $resource->name,
                    'code_number' => $resource->code_number,
                    'phone_number' => $resource->phone_number,
                ] : null;
            }),

            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at,
        ];
    }
}
