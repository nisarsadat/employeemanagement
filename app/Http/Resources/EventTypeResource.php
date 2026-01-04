<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,

            // ✅ works with relation name "specificTypes"
            'event_specific_types' => EventSpecificTypeResource::collection(
                $this->whenLoaded('specificTypes')
            ),
        ];
    }
}
