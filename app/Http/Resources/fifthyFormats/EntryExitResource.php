<?php

namespace App\Http\Resources\fifthyFormats;

use Illuminate\Http\Resources\Json\JsonResource;

class EntryExitResource extends JsonResource
{
    public function toArray($request): array
    {
        // mirror your CaptiveResource pattern
        $related = $this->resource->relationLoaded('resource')
            ? $this->resource->getRelation('resource')
            : null;

        return [
            'id'              => $this->id,
            'name'            => $this->name,

            'entry_way'       => $this->entry_way,
            'way_type'        => $this->way_type,

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
            'Province_addr'               => $this->Province_addr,

            'notes'           => $this->notes,

            // relation ids
            'resource_id'     => $this->resource_id,

            // flattened from related resource
            'resource_code'         => $related?->code_number,
            'resource_phone_number' => $related?->phone_number,

            // keep nested object too
            'resource' => $this->when(
                $related !== null,
                fn () => [
                    'id'           => $related->id,
                    'name'         => $related->name,
                    'code_number'  => $related->code_number,
                    'phone_number' => $related->phone_number,
                ]
            ),

            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
