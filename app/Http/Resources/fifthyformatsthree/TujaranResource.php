<?php

namespace App\Http\Resources\fifthyformatsthree;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TujaranResource extends JsonResource
{
    public function toArray($request): array
    {
        $model = $this->resource;
        $resource = $model->getRelationValue('resource');

        // ✅ picture url like others
        $pictureUrl = null;
        if ($model->picture) {
            if (preg_match('#^https?://#', $model->picture)) {
                $pictureUrl = $model->picture;
            } else {
                $path = $model->picture;
                if (str_starts_with($path, 'storage/')) {
                    $path = substr($path, strlen('storage/'));
                }
                $pictureUrl = Storage::disk('public')->url($path);
            }
        }

        return [
            'id'          => $model->id,
            'name'        => $model->name,
            'father_name' => $model->father_name,

            // ✅ picture
            'picture'     => $model->picture,
            'picture_url' => $pictureUrl,

            'main_province' => $model->main_province,
            'main_district' => $model->main_district,
            'main_village'  => $model->main_village,

            'now_province'       => $model->now_province,
            'now_zone'           => $model->now_zone,
            'now_district'       => $model->now_district,
            'now_village'        => $model->now_village,
            'now_specific_place' => $model->now_specific_place,
            'now_street_number'  => $model->now_street_number,
            'now_home_number'    => $model->now_home_number,

            'old_job' => $model->old_job,
            'now_job' => $model->now_job,
            'organ'   => $model->organ,

            'Province_addr' => $model->Province_addr,

            'guard_guntity' => $model->guard_guntity,
            'phone_number'  => $model->phone_number,
            'people_type'   => $model->people_type,

            'note'        => $model->note,
            'coordinates' => $model->coordinates,

            'resource_id'    => $model->resource_id,
            'resource_code'  => $resource?->code_number,
            'resource_phone' => $resource?->phone_number,

            'resource' => $this->whenLoaded('resource', function () use ($resource) {
                return $resource ? [
                    'id'           => $resource->id,
                    'name'         => $resource->name,
                    'code_number'  => $resource->code_number,
                    'phone_number' => $resource->phone_number,
                ] : null;
            }),

            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at,
        ];
    }
}
