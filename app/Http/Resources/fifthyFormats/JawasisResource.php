<?php

namespace App\Http\Resources\fifthyformats;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JawasisResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,
            'nick_name' => $this->nick_name,
            'father_name' => $this->father_name,

            'main_province' => $this->main_province,
            'main_district' => $this->main_district,
            'main_village' => $this->main_village,

            'now_province' => $this->now_province,
            'now_zone' => $this->now_zone,
            'now_district' => $this->now_district,
            'now_village' => $this->now_village,
            'now_specific_place' => $this->now_specific_place,
            'now_street_number' => $this->now_street_number,
            'now_home_number' => $this->now_home_number,

            'coordinates' => $this->coordinates,
            'country' => $this->country,

            'responsiability' => $this->responsiability,
            'done_activities' => $this->done_activities,
            'activity_place' => $this->activity_place,

            'count_of_communication' => $this->count_of_communication,
            'phone_number' => $this->phone_number,

            'old_job' => $this->old_job,
            'now_job' => $this->now_job,

            'resource_id' => $this->resource_id,
            'notes' => $this->notes,

            /* ===== Picture ===== */
            'picture' => $this->picture
                ? asset('storage/' . $this->picture)
                : null,

            /* ===== Documents ===== */
            'documents' => $this->documents->map(function ($d) {
                return [
                    'id' => $d->id,
                    'name' => $d->original_name,
                    'url' => asset('storage/' . $d->file_path),
                    'mime_type' => $d->mime_type,
                    'size' => $d->size,
                ];
            }),

            /* ===== Resource ===== */
            'resource' => $this->whenLoaded('resource', fn () => [
                'id' => $this->resource->id,
                'name' => $this->resource->name,
                'code_number' => $this->resource->code_number,
                'phone_number' => $this->resource->phone_number,
            ]),
        ];
    }
}
