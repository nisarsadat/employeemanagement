<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                   => $this->id,
            'event_type'           => $this->event_type,
            'event_specific_type'  => $this->event_specific_type,
            'target_person'        => $this->target_person,
            'criminal'             => $this->criminal,
            'event_date'           => $this->event_date,
            'event_hour'           => $this->event_hour,
            'event_zone'           => $this->event_zone,
            'event_hawza'          => $this->event_hawza,
            'event_specific_place' => $this->event_specific_place,
            'talafat_jani'         => $this->talafat_jani,
            'talafat_mali'         => $this->talafat_mali,
            'tafseel'              => $this->tafseel,
            'talks'                => $this->talks,
            'coordinates'          => $this->coordinates,
            'note'                 => $this->note,

            'resource' => $this->whenLoaded('resource', function () {
                if (!$this->resource) return null;
                return [
                    'id'           => $this->resource->id,
                    'name'         => $this->resource->name ?? $this->resource->full_name ?? null,
                    'father_name'  => $this->resource->father_name ?? null,
                    'code_number'  => $this->resource->code_number ?? null,
                ];
            }),

            'office_employee' => $this->whenLoaded('officeEmployee', function () {
                if (!$this->officeEmployee) return null;
                return [
                    'id'           => $this->officeEmployee->id,
                    'name'         => $this->officeEmployee->name ?? $this->officeEmployee->employee_name ?? null,
                    'father_name'  => $this->officeEmployee->father_name ?? null,
                    'code_number'  => $this->officeEmployee->code_number ?? null,
                ];
            }),

            'documents' => $this->whenLoaded('documents'),
        ];
    }
}
