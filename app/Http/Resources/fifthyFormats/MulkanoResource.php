<?php

namespace App\Http\Resources\fifthyFormats;

use Illuminate\Http\Resources\Json\JsonResource;

class MulkanoResource extends JsonResource
{
    public function toArray($request): array
    {
        // Get the related Resource model if it was eager-loaded (exactly like CaptiveResource)
        $related = $this->resource->relationLoaded('resource')
            ? $this->resource->getRelation('resource')
            : null;

        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'picture'             => $this->picture,
            'father_name'         => $this->father_name,
            'grand_father_name'   => $this->grand_father_name,

            'main_province'       => $this->main_province,
            'main_district'       => $this->main_district,
            'main_village'        => $this->main_village,

            'now_province'        => $this->now_province,
            'now_zone'            => $this->now_zone,
            'now_district'        => $this->now_district,
            'now_village'         => $this->now_village,

            'specific_place'      => $this->specific_place,
            'street_number'       => $this->street_number,
            'home_number'         => $this->home_number,
            'guzar'               => $this->guzar,

            'tafseel_sahi'        => $this->tafseel_sahi,
            'street_amount'       => $this->street_amount,
            'old_job'             => $this->old_job,
            'new_job'             => $this->new_job,
            'agent_quantity'      => $this->agent_quantity,
            'home_quantity'       => $this->home_quantity,
            'phone_number'        => $this->phone_number,
            'jorm_history'        => $this->jorm_history,
            'tanzim'              => $this->tanzim,

            'notes'               => $this->notes,

            // relation id
            'resource_id'         => $this->resource_id,

            // ✅ Flatten FROM the related Resource model (same pattern as CaptiveResource)
            'resource_code'         => $related?->code_number,
            'resource_phone_number' => $related?->phone_number,

            // ✅ Keep nested object too, built from the related model
            'resource' => $this->when(
                $related !== null,
                fn () => [
                    'id'           => $related->id,
                    'name'         => $related->name,
                    'code_number'  => $related->code_number,
                    'phone_number' => $related->phone_number,
                ]
            ),

            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
        ];
    }
}
