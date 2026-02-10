<?php

namespace App\Http\Resources\fifthyFormats;

use Illuminate\Http\Resources\Json\JsonResource;

class EntryExitResource extends JsonResource
{
    public function toArray($request): array
    {
        // clean + safe way to access relation
        $related = $this->whenLoaded('resource');

        return [
            'id'              => $this->id,

            'entry_way'       => $this->entry_way,
            'way_type'        => $this->way_type,

            'picture' => $this->picture ? url($this->picture) : null,

            'province'        => $this->province,
            'zone'            => $this->zone,
            'district'        => $this->district,
            'village'         => $this->village,

            'specific_place'  => $this->specific_place,
            'cordinate'       => $this->cordinate,

            'amnayaty_poste'  => $this->amnayaty_poste,
            'amnayaty_ishala' => $this->amnayaty_ishala,

            'where_went'      => $this->where_went,
            'where_come'      => $this->where_come,
            'Province_addr'   => $this->Province_addr,

            'notes'           => $this->notes,

            // relation id
            'resource_id'     => $this->resource_id,

            // flattened from related resource, with fallback
            'resource_code'         => optional($related)->code_number      ?? $this->resource_code,
            'resource_phone_number' => optional($related)->phone_number     ?? $this->resource_phone_number,

            // minimal nested resource
            'resource' => $this->when($related, function () use ($related) {
                return [
                    'id'           => $related->id,
                    'name'         => $related->name,
                    'code_number'  => $related->code_number,
                    'phone_number' => $related->phone_number,
                ];
            }),

            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
