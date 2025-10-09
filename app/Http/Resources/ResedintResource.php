<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResedintResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id"               => $this->id,
            "picture"          => $this->picture ? url($this->picture) : null,

            "name"             => $this->name,
            "father_name"      => $this->father_name,
            "nickname"         => $this->nickname,

            "main_province"    => $this->main_province,
            "main_district"    => $this->main_district,
            "main_village"     => $this->main_village,

            "now_province"     => $this->now_province,
            "now_district"     => $this->now_district,
            "now_village"      => $this->now_village,

            "phone_number"     => $this->phone_number,
            "whatsapp_number"  => $this->whatsapp_number,
            "hawza"            => $this->hawza,

            "zone"             => $this->zone,
            "code_number"      => $this->code_number,

            "officeemployee_id"=> $this->officeemployee_id,
            "notes"            => $this->notes,
        ];
    }
}
