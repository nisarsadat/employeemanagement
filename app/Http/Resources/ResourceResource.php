<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ResourceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'picture' => $this->picture ? url($this->picture) : null,

            'name' => $this->name,
            'father_name' => $this->father_name,
            'grand_father_name' => $this->grand_father_name,
            'nickname' => $this->nickname,
            'tazkira' => $this->tazkira,
            'code_number' => $this->code_number,

            'main_province' => $this->main_province,
            'main_district' => $this->main_district,
            'main_village' => $this->main_village,

            'now_province' => $this->now_province,
            'now_district' => $this->now_district,
            'now_village' => $this->now_village,

            'old_job' => $this->old_job,
            'new_job' => $this->new_job,
            'job_place' => $this->job_place,

            'zone' => $this->zone,
            'hawza' => $this->hawza,
            'specific_place' => $this->specific_place,

            'phone_number' => $this->phone_number,
            'activity' => $this->activity,
            'halat_madani' => $this->halat_madani,
            'age' => $this->age,
            'study_status' => $this->study_status,
            'resource_saved_date' => $this->resource_saved_date,
            'resource_activity_type' => $this->resource_activity_type,
            'resource_cover' => $this->resource_cover,
            'language' => $this->language,
            'nation' => $this->nation,
            'project' => $this->project,

            'resedint_id' => $this->resedint_id,
            'officeemployee_id' => $this->officeemployee_id,

            'notes' => $this->notes,
        ];
    }
}
