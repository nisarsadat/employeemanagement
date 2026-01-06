<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Models\OfficeEmployee;

class OfficeEmployeeResource extends JsonResource
{
    public function toArray($request): array
    {
        // ✅ computed activity based on active resources count (>= 100)
        $activeResourcesCount = (int) ($this->resources_active_count
            ?? ($this->relationLoaded('resources') ? $this->resources->where('activity', OfficeEmployee::ACTIVE)->count() : 0)
        );

        $computedActivity = $activeResourcesCount >= 100
            ? OfficeEmployee::ACTIVE
            : OfficeEmployee::INACTIVE;

        return [
            'id' => $this->id,

            // 🔹 Picture (ORIGINAL logic – unchanged)
            'picture' => $this->picture
                ? (preg_match('#^https?://#', $this->picture)
                    ? $this->picture
                    : (function ($path) {
                        if (str_starts_with($path, 'storage/')) {
                            $path = substr($path, strlen('storage/'));
                        }
                        return Storage::url($path);
                    })($this->picture)
                )
                : null,

            // 🔹 Identity
            'name'              => $this->name,
            'father_name'       => $this->father_name,
            'grand_father_name' => $this->grand_father_name,
            'nick_name'         => $this->nick_name,
            'code_number'       => $this->code_number,

            // 🔹 Rank / Level
            'bast'  => $this->bast,
            'level' => $this->level,

            // 🔹 Background
            'history_jahad'             => $this->history_jahad,
            'dilgay_mesher'             => $this->dilgay_mesher,
            'jahadi_mawaqaf'            => $this->jahadi_mawaqaf,
            'jahad_time_responsibility' => $this->jahad_time_responsibility,
            'tazkira'                   => $this->tazkira,
            'jahad_place'               => $this->jahad_place,

            // 🔹 Main address
            'main_province' => $this->main_province,
            'main_district' => $this->main_district,
            'main_village'  => $this->main_village,

            // 🔹 Current address
            'now_province'       => $this->now_province,
            'now_district'       => $this->now_district,
            'now_village'        => $this->now_village,
            'now_specific_place' => $this->now_specific_place,
            'now_street_number'  => $this->now_street_number,
            'now_home_number'    => $this->now_home_number,

            // 🔹 Related province
            'Province_addr' => $this->Province_addr,

            // 🔹 Job / Org
            'specific_job' => $this->specific_job,
            'qisem'        => $this->qisem,
            'zone'         => $this->zone,
            'hawza'        => $this->hawza,

            // 🔹 Status & Education
            'madani_halat'  => $this->madani_halat,

            // ✅ IMPORTANT: activity now follows your new rule (>=100 active resources)
            'activity'      => $computedActivity,

            'relegy_study'  => $this->relegy_study,
            'new_study'     => $this->new_study,
            'faculty'       => $this->faculty,
            'nizami'        => $this->nizami,
            'job_type'      => $this->job_type,
            'gun_knowledge' => $this->gun_knowledge,

            // 🔹 Demographic
            'age'             => $this->age,
            'specific_skills' => $this->specific_skills,
            'language'        => $this->language,
            'nation'          => $this->nation,
            'payed_money'     => (float) ($this->payed_money ?? 0),

            // 🔹 Contact & misc
            'phone_number' => $this->phone_number,
            'coordinates'  => $this->coordinates,
            'join_date'    => $this->join_date,
            'left_date'    => $this->left_date,
            'note'         => $this->note,

            // 🔹 Documents
            'documents' => $this->whenLoaded('documents', function () {
                return $this->documents->map(function ($doc) {
                    $path = $doc->file_path ?? $doc->path ?? $doc->file_name;

                    return [
                        'id'        => $doc->id,
                        'file_name' => $doc->file_name ?? basename((string) $path),
                        'url'       => $path
                            ? (preg_match('#^https?://#', $path)
                                ? $path
                                : Storage::url($path))
                            : null,
                    ];
                })->toArray();
            }, []),

            // 🔹 Counters (ALL)
            'resources_count' => $this->resources_count
                ?? ($this->relationLoaded('resources') ? $this->resources->count() : 0),

            // ✅ Resources by activity
            'resources_active_count' => $this->resources_active_count
                ?? ($this->relationLoaded('resources') ? $this->resources->where('activity', OfficeEmployee::ACTIVE)->count() : 0),

            'resources_inactive_count' => $this->resources_inactive_count
                ?? ($this->relationLoaded('resources') ? $this->resources->where('activity', OfficeEmployee::INACTIVE)->count() : 0),

            'events_count' => $this->events_count
                ?? ($this->relationLoaded('events') ? $this->events->count() : 0),

            'informations_count' => $this->informations_count
                ?? ($this->relationLoaded('informations') ? $this->informations->count() : 0),

            'tadabiri_informations_count' => $this->tadabiri_informations_count
                ?? ($this->relationLoaded('tadabiriInformations') ? $this->tadabiriInformations->count() : 0),

            'info_informations_count' => $this->info_informations_count
                ?? ($this->relationLoaded('infoInformations') ? $this->infoInformations->count() : 0),

            'documents_count' => $this->documents_count
                ?? ($this->relationLoaded('documents') ? $this->documents->count() : 0),

            // 🔹 Timestamps
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
