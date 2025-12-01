<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InformationResource extends JsonResource
{
    public function toArray($request)
    {
        /** @var \App\Models\Information $info */
        $info = $this->resource; // the Information model instance

        // 🔹 Related models (can be null)
        $resource       = $info ? $info->resource : null;
        $officeEmployee = $info ? $info->officeEmployee : null;
        $documents      = $info ? $info->documents : collect();

        return [
            // --------------------------------------------------
            // 🔹 IDs + flat codes
            // --------------------------------------------------
            'id'                 => $info ? $info->id : null,
            'resource_id'        => $info ? $info->resource_id : null,
            'office_employee_id' => $info ? $info->office_employee_id : null,

            'resource_code_number'        => $resource ? $resource->code_number : null,
            'office_employee_code_number' => $officeEmployee ? $officeEmployee->code_number : null,

            // --------------------------------------------------
            // 🔹 Nested RESOURCE (real related model)
            // --------------------------------------------------
            'resource' => $resource ? [
                'id'          => $resource->id,
                'name'        => $resource->name,
                'code_number' => $resource->code_number,
                'father_name' => $resource->father_name,
            ] : null,

            // --------------------------------------------------
            // 🔹 Nested OFFICE EMPLOYEE
            // --------------------------------------------------
            'office_employee' => $officeEmployee ? [
                'id'          => $officeEmployee->id,
                'name'        => $officeEmployee->name,
                'code_number' => $officeEmployee->code_number,
                'father_name' => $officeEmployee->father_name,
            ] : null,

            // --------------------------------------------------
            // 🔹 Information own fields
            // --------------------------------------------------
            'name'              => $info ? $info->name : null,
            'nick_name'         => $info ? $info->nick_name : null,
            'father_name'       => $info ? $info->father_name : null,
            'grand_father_name' => $info ? $info->grand_father_name : null,

            'main_province'  => $info ? $info->main_province : null,
            'main_districts' => $info ? $info->main_districts : null,
            'main_village'   => $info ? $info->main_village : null,

            'now_province' => $info ? $info->now_province : null,
            'now_district' => $info ? $info->now_district : null,
            'now_village'  => $info ? $info->now_village : null,

            'syasi_info_type'   => $info ? $info->syasi_info_type : null,
            'jenayee_info_type' => $info ? $info->jenayee_info_type : null,
            'Province_addr'     => $info ? $info->Province_addr : null,

            'information_saved_date' => $info ? $info->information_saved_date : null,
            'information_saved_hour' => $info ? $info->information_saved_hour : null,
            'information_degree'     => $info ? $info->information_degree : null,

            'tafseel'         => $info ? $info->tafseel : null,
            'manager_comment' => $info ? $info->manager_comment : null,
            'lead_drirection' => $info ? $info->lead_drirection : null,
            'talks'           => $info ? $info->talks : null,
            'note'            => $info ? $info->note : null,
            'result'          => $info ? $info->result : null,

            // --------------------------------------------------
            // 🔹 Documents
            // --------------------------------------------------
            'documents' => InformationDocumentResource::collection($documents),

            'created_at' => $info ? $info->created_at : null,
            'updated_at' => $info ? $info->updated_at : null,
        ];
    }
}
