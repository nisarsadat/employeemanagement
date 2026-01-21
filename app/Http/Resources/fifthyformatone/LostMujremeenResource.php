<?php

namespace App\Http\Resources\fifthyformatone;

use Illuminate\Http\Resources\Json\JsonResource;

class LostMujremeenResource extends JsonResource
{
    public function toArray($request): array
    {
        $related = $this->relationLoaded('resource')
            ? $this->getRelation('resource')
            : null;

        return [
            'id'          => $this->id,
            'picture'     => $this->picture,

            'nickname'    => $this->nickname,
            'name'        => $this->name,
            'father_name' => $this->father_name,

            'main_province' => $this->main_province,
            'main_district' => $this->main_district,
            'main_village'  => $this->main_village,

            'now_province'       => $this->now_province,
            'now_district'       => $this->now_district,
            'now_village'        => $this->now_village,
            'now_specific_place' => $this->now_specific_place,
            'now_street_number'  => $this->now_street_number,
            'now_home_number'    => $this->now_home_number,

            'Province_addr' => $this->Province_addr,

            'province' => $this->province,
            'zone'     => $this->zone,
            'district' => $this->district,

            'old_job'      => $this->old_job,
            'jorm'         => $this->jorm,
            'history_jorm' => $this->history_jorm,
            'lost_reason'  => $this->lost_reason,

            'phone_number' => $this->phone_number,
            'note'         => $this->note,

            // resource fields
            'resource_id'    => $this->resource_id,
            'resource_code'  => $related?->code_number,
            'resource_phone' => $related?->phone_number,
            'coordinates'    => $related?->coordinates,

            'resource' => $this->when(
                $related !== null,
                fn () => [
                    'id'           => $related->id,
                    'name'         => $related->name,
                    'code_number'  => $related->code_number,
                    'phone_number' => $related->phone_number,
                ]
            ),

            // ✅ documents
            'documents' => $this->whenLoaded('documents', function () {
                return $this->documents->map(fn ($d) => [
                    'id'            => $d->id,
                    'file_path'     => $d->file_path,
                    'original_name' => $d->original_name,
                    'mime_type'     => $d->mime_type,
                    'size'          => $d->size,
                    'created_at'    => $d->created_at,
                ]);
            }, []),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
