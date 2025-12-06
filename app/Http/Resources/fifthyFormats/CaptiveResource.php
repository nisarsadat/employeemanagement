<?php

namespace App\Http\Resources\fifthyFormats;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CaptiveResource extends JsonResource
{
    public function toArray($request): array
    {
        $related = $this->resource->relationLoaded('resource')
            ? $this->resource->getRelation('resource')
            : null;

        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'picture'          => $this->picture,
            'nick_name'        => $this->nick_name,
            'father_name'      => $this->father_name,
            'main_province'    => $this->main_province,
            'main_district'    => $this->main_district,
            'main_village'     => $this->main_village,
            'now_province'     => $this->now_province,
            'now_zone'         => $this->now_zone,
            'now_district'     => $this->now_district,
            'now_village'      => $this->now_village,
            'specific_place'   => $this->specific_place,
            'street_number'    => $this->street_number,
            'home_number'      => $this->home_number,
            'specific_form'    => $this->specific_form,
            'band_distince'    => $this->band_distince,
            'now_activity'     => $this->now_activity,
            'phone_number'     => $this->phone_number,
            'note'             => $this->note,
            'Province_addr'    => $this->Province_addr,
            'coordinates'      => $this->coordinates,

            'resource_id' => $this->resource_id,

            'resource_code'         => $related?->code_number,
            'resource_phone_number' => $related?->phone_number,

            'resource' => $this->when(
                $related !== null,
                fn () => [
                    'id'           => $related->id,
                    'name'         => $related->name,
                    'code_number'  => $related->code_number,
                    'phone_number' => $related->phone_number,
                ]
            ),

            // ✅ documents list
            'documents' => $this->whenLoaded('documents', function () {
                return $this->documents->map(function ($doc) {
                    return [
                        'id'         => $doc->id,
                        'file_name'  => $doc->file_name,
                        'file_path'  => $doc->file_path,
                        'url'        => Storage::disk('public')->url($doc->file_path),
                        'mime_type'  => $doc->mime_type,
                        'size_bytes' => $doc->size_bytes,
                        'created_at' => $doc->created_at,
                    ];
                });
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
