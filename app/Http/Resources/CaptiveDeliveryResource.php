<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CaptiveDeliveryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                   => $this->id,
            'picture'              => $this->picture ? asset('storage/'.$this->picture) : null,

            'name'                 => $this->name,
            'father_name'          => $this->father_name,
            'nickname'             => $this->nickname,
            'grand_father_name'    => $this->grand_father_name,

            'main_province'        => $this->main_province,
            'main_district'        => $this->main_district,
            'main_village'         => $this->main_village,

            'now_province'         => $this->now_province,
            'now_district'         => $this->now_district,
            'now_village'          => $this->now_village,

            'halat_madani'         => $this->halat_madani,
            'age'                  => $this->age,
            'tazkira'              => $this->tazkira,
            'criminal_history'     => $this->criminal_history,

            'old_job'              => $this->old_job,
            'new_job'              => $this->new_job,

            'language'             => $this->language,
            'scientific_level'     => $this->scientific_level,

            'captive_place'        => $this->captive_place,
            'captive_marja'        => $this->captive_marja,
            'captive_date'         => $this->captive_date,
            'captive_hour'         => $this->captive_hour,

            'suspect_type'         => $this->suspect_type,
            'phone_number'         => $this->phone_number,
            'captive_halat'        => $this->captive_halat,
            'suspect_tools'        => $this->suspect_tools,
            'Province_addr'               => $this->Province_addr,

            'notes'                => $this->notes,

            'documents'            => CaptiveDeliveryDocumentResource::collection($this->whenLoaded('documents')),

            'created_at'           => $this->created_at,
            'updated_at'           => $this->updated_at,
            'coordinates'          => $this->coordinates,
             
        ];
    }
}
