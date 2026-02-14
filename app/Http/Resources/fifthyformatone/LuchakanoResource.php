<?php

namespace App\Http\Resources\fifthyformatone;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class LuchakanoResource extends JsonResource
{
    public function toArray($request): array
    {
        // Safe accessor (only reads if relation is already loaded)
        $res = $this->resource->getRelationValue('resource');

        // ✅ Mawad style picture_url
        $pictureUrl = null;
        if ($this->picture) {
            if (preg_match('#^https?://#', $this->picture)) {
                $pictureUrl = $this->picture;
            } else {
                $path = $this->picture;

                // normalize if it was stored like "storage/..."
                if (str_starts_with($path, 'storage/')) {
                    $path = substr($path, strlen('storage/'));
                }

                $pictureUrl = Storage::disk('public')->url($path);
            }
        }

        return [
            'id'                 => $this->id,

            'nickname'           => $this->nickname,
            'name'               => $this->name,
            'father_name'        => $this->father_name,

            // ✅ raw + url
            'picture'            => $this->picture,
            'picture_url'        => $pictureUrl,

            'main_province'      => $this->main_province,
            'main_district'      => $this->main_district,
            'main_village'       => $this->main_village,

            'now_province'       => $this->now_province,
            'now_zone'           => $this->now_zone,
            'now_district'       => $this->now_district,
            'now_village'        => $this->now_village,
            'now_specific_place' => $this->now_specific_place,
            'now_street_number'  => $this->now_street_number,
            'now_home_number'    => $this->now_home_number,
            'Province_addr'      => $this->Province_addr,

            'old_job'            => $this->old_job,
            'job'                => $this->job,
            'gun_type'           => $this->gun_type,
            'phone_number'       => $this->phone_number,
            'history_jorm'       => $this->history_jorm,
            'note'               => $this->note,
            'supplier'               => $this->supplier,
            'takhlof_type'               => $this->takhlof_type,
            'coordinates'        => $this->coordinates,

            // FK
            'resource_id'        => $this->resource_id,

            // Flattened related fields
            'resource_code'      => $res->code_number  ?? null,
            'resource_phone'     => $res->phone_number ?? null,

            // Minimal embedded resource (only if eager-loaded)
            'resource' => $this->whenLoaded('resource', function () use ($res) {
                return [
                    'id'           => $res->id ?? null,
                    'name'         => $res->name ?? null,
                    'code_number'  => $res->code_number ?? null,
                    'phone_number' => $res->phone_number ?? null,
                ];
            }),

            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
        ];
    }
}
