<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventSpecificTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'event_type_id' => $this->event_type_id,
            'name'          => $this->name,

            // ✅ this is all frontend needs
            'event_type_name' => $this->whenLoaded('eventType', function () {
                return $this->eventType?->name;
            }),
        ];
    }
}
