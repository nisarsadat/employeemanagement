<?php

namespace App\Http\Resources\fifthyformatone;

use Illuminate\Http\Resources\Json\JsonResource;

class MawadResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                => $this->id,
            'mawad_type'        => $this->mawad_type,
            'nickname'          => $this->nickname,
            'name'              => $this->name,
            'father_name'       => $this->father_name,

            // --- Main Address ---
            'main_province'     => $this->main_province,
            'main_district'     => $this->main_district,
            'main_village'      => $this->main_village,

            // --- Current Address ---
            'now_province'      => $this->now_province,
            'now_district'      => $this->now_district,
            'now_village'       => $this->now_village,

            // --- Extra Location Block ---
            'province'          => $this->province,
            'zone'              => $this->zone,
            'district'          => $this->district,
            'village'           => $this->village,

            // --- Other Info ---
            'specific_place'    => $this->specific_place,
            'coordinates'       => $this->coordinates,
            'mawad_sales_phone' => $this->mawad_sales_phone,

            'history_form'      => $this->history_form,
            'supplier'          => $this->supplier,
            'note'              => $this->note,

            // --- Resource Fields (flat + relation) ---
            'resource_code'     => $this->resource->code_number ?? null,
            'resource_phone'    => $this->resource->phone_number ?? null,

            'resource_id'       => $this->resource_id,
            'resource'          => $this->whenLoaded('resource', fn () => [
                'id'   => $this->resource->id,
                'name' => $this->resource->name ?? null,
            ]),

            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}
