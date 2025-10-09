<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ZoneEmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            "id"              => $this->id,
            "picture"         => $this->picture ? url($this->picture) : null,

            "name"            => $this->name,
            "father_name"     => $this->father_name,
            "nickname"        => $this->nickname,

            "code_number"     => $this->code_number,
            "hawza"           => $this->hawza,
            "zone"            => $this->zone,
            "degree"          => $this->degree,
            "zone_entry_hour" => $this->zone_entry_hour,

            "resedint_id"     => $this->resedint_id,
            
            "notes"           => $this->notes,
        ];
    }
}
