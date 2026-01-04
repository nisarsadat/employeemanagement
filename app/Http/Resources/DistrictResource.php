<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistrictResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'province_id' => $this->province_id,
            'name'        => $this->name,

            // flattened fields
            'province_name' => $this->whenLoaded('province', fn () => $this->province?->name),

            // minimal embed
            'province' => $this->whenLoaded('province', fn () => [
                'id'   => $this->province?->id,
                'name' => $this->province?->name,
            ]),
        ];
    }
}
