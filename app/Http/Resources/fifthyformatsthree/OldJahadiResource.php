<?php

namespace App\Http\Resources\fifthyformatsthree;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OldJahadiResource extends JsonResource
{
    public function toArray($request): array
    {
        $resource = $this->resource->getRelationValue('resource');

        // ✅ picture_url (public disk) — same pattern as your other modules
        $pictureUrl = null;
        if (!empty($this->picture)) {
            if (preg_match('#^https?://#', (string) $this->picture)) {
                $pictureUrl = $this->picture;
            } else {
                $path = (string) $this->picture;
                if (str_starts_with($path, 'storage/')) {
                    $path = substr($path, strlen('storage/'));
                }
                $pictureUrl = Storage::disk('public')->url($path);
            }
        }

        return [
            'id' => $this->id,
            'nick_name' => $this->nick_name,
            'name' => $this->name,
            'father_name' => $this->father_name,

            // ✅ picture
            'picture' => $this->picture,
            'picture_url' => $pictureUrl,

            'main_province' => $this->main_province,
            'main_district' => $this->main_district,
            'main_village'  => $this->main_village,

            'now_province' => $this->now_province,
            'now_zone' => $this->now_zone,
            'now_district' => $this->now_district,
            'now_village' => $this->now_village,
            'now_specific_place' => $this->now_specific_place,
            'now_street_number' => $this->now_street_number,
            'now_home_number' => $this->now_home_number,

            'old_job' => $this->old_job,
            'now_job' => $this->now_job,
            'employee_quantity' => $this->employee_quantity,
            'supplier' => $this->supplier,
            'tanzim' => $this->tanzim,
            'organ' => $this->organ,
            'Province_addr' => $this->Province_addr,

            'note' => $this->note,
            'coordinates' => $this->coordinates,

            'resource_id' => $this->resource_id,
            'resource_code' => $resource?->code_number,
            'resource_phone' => $resource?->phone_number,

            'resource' => $this->whenLoaded('resource', function () use ($resource) {
                return $resource ? [
                    'id' => $resource->id,
                    'name' => $resource->name,
                    'code_number' => $resource->code_number,
                    'phone_number' => $resource->phone_number,
                ] : null;
            }),
        ];
    }
}
