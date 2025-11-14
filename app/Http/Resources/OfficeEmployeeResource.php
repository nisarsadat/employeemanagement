<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OfficeEmployeeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                      => $this->id,
            'picture'                 => $this->picture,

            'name'                    => $this->name,
            'father_name'             => $this->father_name,
            'grand_father_name'       => $this->grand_father_name,
            'nick_name'               => $this->nick_name,
            'code_number'             => $this->code_number,

            'bast'                    => $this->bast,
            'level'                   => $this->level,
            'history_jahad'           => $this->history_jahad,
            'dilgay_mesher'           => $this->dilgay_mesher,
            'jahadi_mawaqaf'          => $this->jahadi_mawaqaf,
            'jahad_time_responsibility'=> $this->jahad_time_responsibility,
            'tazkira'                 => $this->tazkira,

            'jahad_place'             => $this->jahad_place,
            'main_province'           => $this->main_province,
            'main_district'           => $this->main_district,
            'main_village'            => $this->main_village,

            'now_province'            => $this->now_province,
            'now_district'            => $this->now_district,
            'now_village'             => $this->now_village,
            'now_specific_place'      => $this->now_specific_place,
            'now_street_number'       => $this->now_street_number,
            'now_home_number'         => $this->now_home_number,
            'Province_addr'               => $this->Province_addr,

            'specific_job'            => $this->specific_job,
            'qisem'                   => $this->qisem,
            'zone'                    => $this->zone,
            'hawza'                   => $this->hawza,

            'madani_halat'            => $this->madani_halat,
            'activity'                => $this->activity,
            'relegy_study'            => $this->relegy_study,
            'new_study'               => $this->new_study,
            'faculty'                 => $this->faculty,
            'nizami'                  => $this->nizami,
            'gun_knowledge'           => $this->gun_knowledge,

            'age'                     => $this->age,
            'specific_skills'         => $this->specific_skills,
            'language'                => $this->language,
            'nation'                  => $this->nation,

            'phone_number'            => $this->phone_number,
            'coordinates'             => $this->coordinates,
            'join_date'               => $this->join_date,
            'left_date'               => $this->left_date,
            'note'                    => $this->note,

            'documents' => $this->whenLoaded('documents', function () {
                return $this->documents->map(function ($d) {
                    return [
                        'id'         => $d->id,
                        'file_name'  => $d->file_name,
                        'mime_type'  => $d->mime_type,
                        'size_bytes' => $d->size_bytes,
                        'file_path'  => $d->file_path,
                        'url'        => Storage::disk('public')->url($d->file_path),
                    ];
                });
            }),
                    // ✅ Relation counts (flattened)
        'events_count'           => $this->events_count ?? 0,
        'resources_count'        => $this->resources_count ?? 0,
        'informations_count'     => $this->informations_count ?? 0,
        'documents_count'        => $this->documents_count
                                     ?? ($this->relationLoaded('documents') ? $this->documents->count() : 0),

        // ✅ Optional: grouped counts block (nice for UI)
        'counts' => [
            'events'          => $this->events_count ?? 0,
            'resources'       => $this->resources_count ?? 0,
            'informations'    => $this->informations_count ?? 0,
            'documents'       => $this->documents_count
                                       ?? ($this->relationLoaded('documents') ? $this->documents->count() : 0),
            'total_events'    => ($this->events_count ?? 0) + ($this->assigned_events_count ?? 0),
        ],
        ];
    }
}
