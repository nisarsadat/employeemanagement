<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class WakilanoResource extends JsonResource
{
    public function toArray($request): array
    {
        $model = $this->resource;

        // ✅ Safely get the related resource if it's loaded
        $related = $model->relationLoaded('resource')
            ? $model->getRelation('resource')
            : $model->getRelationValue('resource');

        // ✅ picture url (same style as others)
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
            'id'                 => $model->id,

            // ✅ picture
            'picture'            => $model->picture,
            'picture_url'        => $pictureUrl,

            'coordinates'        => $model->coordinates,
            'name'               => $model->name,
            'f_name'             => $model->f_name,
            'grand_father_name'  => $model->grand_father_name,

            'main_province'      => $model->main_province,
            'main_district'      => $model->main_district,
            'main_village'       => $model->main_village,

            'now_province'       => $model->now_province,
            'now_zone'           => $model->now_zone,
            'now_district'       => $model->now_district,
            'now_village'        => $model->now_village,
            'now_specific_place' => $model->now_specific_place,
            'now_street_number'  => $model->now_street_number,
            'now_home_number'    => $model->now_home_number,

            'Guzar'              => $model->Guzar,
            'place_tafseel'      => $model->place_tafseel,
            'old_job'            => $model->old_job,
            'now_job'            => $model->now_job,
            'home_quantity'      => $model->home_quantity,
            'phone_number'       => $model->phone_number,
            'history_form'       => $model->history_form,

            'Province_addr'      => $model->Province_addr,
            'note'               => $model->note,

            // ✅ resource fields (flattened)
            'resource_id'        => $model->resource_id,
            'resource_code'      => $related?->code_number,
            'resource_phone'     => $related?->phone_number,
            'resource_phone_number' => $related?->phone_number,

            // ✅ minimal embedded resource object when loaded
            'resource' => $this->when(
                $related !== null,
                fn () => [
                    'id'           => $related->id,
                    'name'         => $related->name,
                    'code_number'  => $related->code_number,
                    'phone_number' => $related->phone_number,
                ]
            ),

            'created_at'         => $model->created_at,
            'updated_at'         => $model->updated_at,
        ];
    }
}
