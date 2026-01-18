<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'resource_id' => $this->resource_id,
            'office_employee_id' => $this->office_employee_id,
            'office_employee_akhistonky_id' => $this->office_employee_akhistonky_id,

            'event_type' => $this->event_type,
            'event_specific_type' => $this->event_specific_type,
            'target_person' => $this->target_person,
            'criminal' => $this->criminal,
            'event_date' => $this->event_date,
            'event_hour' => $this->event_hour,

            'event_zone' => $this->event_zone,
            'event_hawza' => $this->event_hawza,
            'event_specific_place' => $this->event_specific_place,

            'talafat_jani' => $this->talafat_jani,
            'talafat_mali' => $this->talafat_mali,
            'injured' => $this->injured,
            'killed' => $this->killed,
            'trust_percentage' => $this->trust_percentage,

            'tafseel' => $this->tafseel,
            'talks' => $this->talks,
            'coordinates' => $this->coordinates,
            'note' => $this->note,
            'docs_note' => $this->docs_note,
            'Province_addr' => $this->Province_addr,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // relations
            'resource' => $this->whenLoaded('resource'),
            'office_employee' => $this->whenLoaded('officeEmployee'),
            'akhistonky_employee' => $this->whenLoaded('akhistonkyEmployee'),

            // ✅ NEW: codes for table display
            'resource_code' => optional($this->resource)->code_number,
            'office_employee_code' => optional($this->officeEmployee)->code_number,
            'office_employee_akhistonky_code' => optional($this->akhistonkyEmployee)->code_number,

            'documents' => $this->whenLoaded('documents', function () {
                return $this->documents->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'file_name' => $doc->file_name,
                        'file_path' => $doc->file_path,
                        'mime_type' => $doc->mime_type,
                        'size_bytes' => $doc->size_bytes,
                        'url' => $doc->file_path ? asset('storage/' . $doc->file_path) : null,
                        'created_at' => $doc->created_at ?? null,
                    ];
                });
            }),
        ];
    }
}
