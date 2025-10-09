<?php
// app/Http/Resources/InformationResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InformationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'   => $this->id,

            // codes
            'information_code'     => $this->information_code,
            'office_employee_code' => $this->office_employee_code,
            'zone_employee_code'   => $this->zone_employee_code,

            // intro
            'name'              => $this->name,
            'father_name'       => $this->father_name,
            'nickname'          => $this->nickname,
            'grand_father_name' => $this->grand_father_name,

            // main
            'main_province' => $this->main_province,
            'main_district' => $this->main_district,
            'main_village'  => $this->main_village,

            // now
            'now_province' => $this->now_province,
            'now_district' => $this->now_district,
            'now_village'  => $this->now_village,

            // typing
            'seyasi_type'        => $this->seyasi_type,
            'jenayee_type'       => $this->jenayee_type,
            'information_type'   => $this->information_type,
            'information_date'   => $this->information_date,
            'information_hour'   => $this->information_hour,
            'importance_degree'  => $this->importance_degree,
            'information_category'=> $this->information_category,

            // relations
            'officeemployee_id' => $this->officeemployee_id,
            'zone_employee_id'  => $this->zone_employee_id,

            // text
            'tafseel' => $this->tafseel,
            'talks'   => $this->talks,
            'outcome' => $this->outcome,
            'notes'   => $this->notes,

            // docs
            'documents' => InformationDocumentResource::collection($this->whenLoaded('documents')),
        ];
    }
}
