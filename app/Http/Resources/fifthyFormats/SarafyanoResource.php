<?php

namespace App\Http\Resources\fifthyFormats;

use Illuminate\Http\Resources\Json\JsonResource;

class SarafyanoResource extends JsonResource
{
    public function toArray($request): array
    {
        // mirror your CaptiveResource pattern
        $related = $this->resource->relationLoaded('resource')
            ? $this->resource->getRelation('resource')
            : null;

        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'sarafi_name'        => $this->sarafi_name,
            'sarafi_type'        => $this->sarafi_type,
            'father_name'        => $this->father_name,
            'position'           => $this->position,
            'phone_number'       => $this->phone_number,
            'picture' => $this->picture ? url($this->picture) : null,

            'province'           => $this->province,
            'zone'               => $this->zone,
            'district'           => $this->district,
            'village'            => $this->village,
            'specific_place'     => $this->specific_place,

            'market_name'        => $this->market_name,
            'shop_number'        => $this->shop_number,
            'cordinate'          => $this->cordinate,

            'provinces_hawala'   => $this->provinces_hawala,
            'countries_hawala'   => $this->countries_hawala,
            'employee_quantity'  => $this->employee_quantity,

            'notes'              => $this->notes,

            // relation id
            'resource_id'        => $this->resource_id,

            // flattened from related resource
            'resource_code'         => $related?->code_number,
            'resource_phone_number' => $related?->phone_number,
            'Province_addr'               => $this->Province_addr,

            // keep nested too
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
