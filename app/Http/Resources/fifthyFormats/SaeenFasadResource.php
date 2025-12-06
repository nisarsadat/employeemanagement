<?php

namespace App\Http\Resources\fifthyFormats;

use Illuminate\Http\Resources\Json\JsonResource;

class SaeenFasadResource extends JsonResource
{
    public function toArray($request): array
    {
        // ✅ Safely get the related resource if it's loaded
        $related = $this->relationLoaded('resource')
            ? $this->getRelation('resource')
            : null;

        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'picture'       => $this->picture,

            'nick_name'     => $this->nick_name,
            'father_name'   => $this->father_name,

            // 🏠 Main address
            'main_province' => $this->main_province,
            'main_district' => $this->main_district,
            'main_village'  => $this->main_village,

            // 🏠 Current address
            'now_province'  => $this->now_province,
            'now_zone'      => $this->now_zone,
            'now_district'  => $this->now_district,
            'now_village'   => $this->now_village,

            'specific_place'=> $this->specific_place,
            'street_number' => $this->street_number,
            'home_number'   => $this->home_number,

            // 📌 Extra fields
            'responsiability'       => $this->responsiability,
            'activity_place'        => $this->activity_place,
            'friends_number'        => $this->friends_number,
            'phone_number'          => $this->phone_number,
            'band_distince'         => $this->band_distince,
            'band_place'            => $this->band_place,
            'old_job'               => $this->old_job,
            'now_job'               => $this->now_job,
            'note'                  => $this->note,
            'Province_addr'         => $this->Province_addr,
            'coordinates'           => $this->coordinates,

            // 🔗 Resource fields
            'resource_id'           => $this->resource_id,

            // prefer explicit columns, but fall back to relation if relation is loaded
            'resource_code'         => $this->resource_code
                                        ?? $related?->code_number,
            'resource_phone_number' => $this->resource_phone_number
                                        ?? $related?->phone_number,

            'resource' => $this->when(
                $related !== null,
                fn () => [
                    'id'           => $related->id,
                    'name'         => $related->name,
                    'code_number'  => $related->code_number,
                    'phone_number' => $related->phone_number,
                ]
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
