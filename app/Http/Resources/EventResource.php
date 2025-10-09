<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'event_type' => $this->event_type,
            'event_specific_type' => $this->event_specific_type,
            'target_person' => $this->target_person,
            'who_did' => $this->who_did,
            'event_date' => $this->event_date,
            'event_hour' => $this->event_hour,
            'zone' => $this->zone,
            'hawza' => $this->hawza,
            'specific_place' => $this->specific_place,
            'jani_talafat' => $this->jani_talafat,
            'mali_talafat' => $this->mali_talafat,
            'tafseel' => $this->tafseel,
            'talks' => $this->talks,
            'notes' => $this->notes,

            'resource' => $this->whenLoaded('resource'),
            'office_employee' => $this->whenLoaded('officeEmployee'),
            'zone_employee' => $this->whenLoaded('zoneEmployee'),
            'event_office_employee' => $this->whenLoaded('eventOfficeEmployee'),
            'event_zone_employee' => $this->whenLoaded('eventZoneEmployee'),
            'documents' => $this->whenLoaded('documents'),
        ];
    }
}
