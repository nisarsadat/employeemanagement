<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InformationResource extends JsonResource
{
    public function toArray($request)
    {
        /** @var \App\Models\Information $info */
        $info = $this->resource;

        $resource  = $info ? $info->resource : null;
        $documents = $info ? $info->documents : collect();

        // ✅ Pick the correct office employee (fallback to tadabiri/info)
        $officeEmployee = null;
        if ($info) {
            $officeEmployee =
                $info->officeEmployee
                ?? $info->tadabiriEmployee
                ?? $info->officeEmployeeInfo;
        }

        // Date formatting
        $rawDate       = $info ? (string) $info->information_saved_date : null;
        $cleanDate     = $rawDate ? substr($rawDate, 0, 10) : null;
        $formattedDate = $cleanDate ? str_replace('-', '/', $cleanDate) : null;

        return [
            'id'                 => $info ? $info->id : null,
            'resource_id'        => $info ? $info->resource_id : null,

            'office_employee_id' => $info ? $info->office_employee_id : null,
            'office_employee_info_id' => $info ? $info->office_employee_info_id : null,
            'office_employee_info_code_number' => $info?->officeEmployeeInfo?->code_number,


            'resource_code_number'        => $resource ? $resource->code_number : null,

            // ✅ This will now return the code_number of whichever employee is linked
            'office_employee_code_number' => $officeEmployee ? $officeEmployee->code_number : null,

            'resource' => $resource ? [
                'id'          => $resource->id,
                'name'        => $resource->name,
                'code_number' => $resource->code_number,
                'father_name' => $resource->father_name,
            ] : null,

            // ✅ Nested office_employee now also follows the same fallback
            'office_employee' => $officeEmployee ? [
                'id'          => $officeEmployee->id,
                'name'        => $officeEmployee->name,
                'code_number' => $officeEmployee->code_number,
                'father_name' => $officeEmployee->father_name,
            ] : null,

            'name'              => $info ? $info->name : null,
            'nick_name'         => $info ? $info->nick_name : null,
            'father_name'       => $info ? $info->father_name : null,
            'grand_father_name' => $info ? $info->grand_father_name : null,
            'madani_halat'      => $info ? $info->madani_halat : null,
            'job'               => $info ? $info->job : null,
            'age'               => $info ? $info->age : null,
            'estijalyat_degree' => $info ? $info->estijalyat_degree : null,

            'main_province'  => $info ? $info->main_province : null,
            'main_districts' => $info ? $info->main_districts : null,
            'main_village'   => $info ? $info->main_village : null,

            'hamahangi_date' => $this->hamahangi_date,
            'maktop_number'  => $this->maktop_number,
            'organ'          => $this->organ,


            'now_province' => $info ? $info->now_province : null,
            'now_district' => $info ? $info->now_district : null,
            'now_village'  => $info ? $info->now_village : null,

            'syasi_info_type'   => $info ? $info->syasi_info_type : null,
            'jenayee_info_type' => $info ? $info->jenayee_info_type : null,
            'Province_addr'     => $info ? $info->Province_addr : null,

            'information_saved_date'     => $cleanDate,
            'information_saved_date_ymd' => $formattedDate,

            'information_saved_hour' => $info ? $info->information_saved_hour : null,
            'information_degree'     => $info ? $info->information_degree : null,
            'trust_percentage'     => $info ? $info->trust_percentage : null,

            'tafseel'         => $info ? $info->tafseel : null,
            'manager_comment' => $info ? $info->manager_comment : null,
            'lead_drirection' => $info ? $info->lead_drirection : null,
            'talks'           => $info ? $info->talks : null,
            'note'            => $info ? $info->note : null,
            'docs_note'       => $info ? $info->docs_note : null,
            'result'          => $info ? $info->result : null,

            'documents' => InformationDocumentResource::collection($documents),

            'created_at' => $info ? $info->created_at : null,
            'updated_at' => $info ? $info->updated_at : null,
        ];
    }
}
