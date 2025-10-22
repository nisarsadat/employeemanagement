<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InformationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,

            'resource' => $this->whenLoaded('resource', function () {
                return [
                    'id'          => $this->resource->id,
                    'name'        => $this->resource->name,
                    'code_number' => $this->resource->code_number,
                    'father_name' => $this->resource->father_name,
                ];
            }),

            'office_employee' => $this->whenLoaded('officeEmployee', function () {
                return [
                    'id'          => $this->officeEmployee->id,
                    'name'        => $this->officeEmployee->name,
                    'code_number' => $this->officeEmployee->code_number,
                    'father_name' => $this->officeEmployee->father_name,
                ];
            }),

            'name'              => $this->name,
            'nick_name'         => $this->nick_name,
            'father_name'       => $this->father_name,
            'grand_father_name' => $this->grand_father_name,

            'main_province'  => $this->main_province,
            'main_districts' => $this->main_districts,
            'main_village'   => $this->main_village,

            'now_province' => $this->now_province,
            'now_district' => $this->now_district,
            'now_village'  => $this->now_village,

            'syasi_info_type'   => $this->syasi_info_type,
            'jenayee_info_type' => $this->jenayee_info_type,

            'information_saved_date' => $this->information_saved_date,
            'information_saved_hour' => $this->information_saved_hour,
            'information_degree'     => $this->information_degree,

            'tafseel'         => $this->tafseel,
            'manager_comment' => $this->manager_comment,
            'lead_drirection' => $this->lead_drirection,
            'talks'           => $this->talks,
            'note'            => $this->note,

            'documents' => InformationDocumentResource::collection($this->whenLoaded('documents')),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
