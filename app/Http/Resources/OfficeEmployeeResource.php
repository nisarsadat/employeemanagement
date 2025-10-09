<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficeEmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "picture" => $this->picture ? url($this->picture) : null,

            "name" => $this->name,
            "father_name" => $this->father_name,
            "nickname" => $this->nickname,

            "main_province" => $this->main_province,
            "main_district" => $this->main_district,
            "main_village" => $this->main_village,

            "now_province" => $this->now_province,
            "now_district" => $this->now_district,
            "now_village" => $this->now_village,

            "releg_study" => $this->releg_study,
            "new_study" => $this->new_study,

            "phone_number" => $this->phone_number,
            "tazkira" => $this->tazkira,

            "apply_date" => $this->apply_date,
            "join_date" => $this->join_date,
            "left_date" => $this->left_date,
            'code_number' => $this->code_number,
            "notes" => $this->notes,
        ];
    }
}
