<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class WakilanoResource extends JsonResource
{
    public function toArray($request): array
    {
        // ✅ Safely get the related resource if it's loaded
        $related = $this->relationLoaded('resource')
            ? $this->getRelation('resource')
            : null;

        return [
            'id'                 => $this->id,
            'coordinates'        => $this->coordinates,
            'name'               => $this->name,
            'f_name'             => $this->f_name,
            'grand_father_name'  => $this->grand_father_name,

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

            'Guzar'              => $this->Guzar,
            'place_tafseel'      => $this->place_tafseel,
            'old_job'            => $this->old_job,
            'now_job'            => $this->now_job,
            'home_quantity'      => $this->home_quantity,
            'phone_number'       => $this->phone_number,
            'history_form'       => $this->history_form,

            'Province_addr'      => $this->Province_addr,
            'note'               => $this->note,

            // ✅ resource fields (flattened)
            'resource_id'        => $this->resource_id,
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

            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
        ];
    }
}
