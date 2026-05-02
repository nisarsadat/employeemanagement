<?php

namespace App\Http\Resources\fifthyFormats;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FasadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
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

            'specific_place' => $this->specific_place,
            'street_number' => $this->street_number,
            'home_number' => $this->home_number,

            'jabha' => $this->jabha,
            'supplier_name' => $this->supplier_name,
            'responsibility' => $this->responsibility,
            'activity_zone' => $this->activity_zone,
            'friends_number' => $this->friends_number,
            'phone_number' => $this->phone_number,
            'bond_distance' => $this->bond_distance,
            'last_band_reason' => $this->last_band_reason,
            'band_place' => $this->band_place,
            'now_job' => $this->now_job,
            'tafseel' => $this->tafseel,

            'resource_code' => $this->resource_code,
            'resource_phone' => $this->resource_phone,
            'resource_id' => $this->resource_id,
            'Province_addr' => $this->Province_addr,
            'coordinates' => $this->coordinates,
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
