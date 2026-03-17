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
        $matchedInformation = $info ? $info->matchedInformation : null;

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

            'matched_information_id' => $info ? $info->matched_information_id : null,
            'matched_information' => $matchedInformation ? [
                'id' => $matchedInformation->id,
                'name' => $matchedInformation->name,
                'father_name' => $matchedInformation->father_name,
                'main_province' => $matchedInformation->main_province,
                'main_districts' => $matchedInformation->main_districts,
                'main_village' => $matchedInformation->main_village,
            ] : null,

            'resource_code_number'        => $resource ? $resource->code_number : null,

            'office_employee_code_number' => $officeEmployee ? $officeEmployee->code_number : null,

            'resource' => $resource ? [
                'id'          => $resource->id,
                'name'        => $resource->name,
                'code_number' => $resource->code_number,
                'father_name' => $resource->father_name,
            ] : null,

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
            'tahlil'            => $info ? $info->tahlil : null,
            'estijalyat_degree' => $info ? $info->estijalyat_degree : null,
            'information_category' => $info ? $info->information_category : null,
            'resource_lasrasy' => $info ? $info->resource_lasrasy : null,
            'coordinates' => $info ? $info->coordinates : null,

            'main_province'  => $info ? $info->main_province : null,
            'main_districts' => $info ? $info->main_districts : null,
            'main_village'   => $info ? $info->main_village : null,

            'specefic_place'  => $info ? $info->specefic_place : null,
            'home_number' => $info ? $info->home_number : null,
            'street_number'   => $info ? $info->street_number : null,

            'account_id' => $this->account_id,
            'account' => $this->whenLoaded('account', function () {
                return [
                    'id' => $this->account->id,
                    'name' => $this->account->name,
                    'price' => $this->account->price,
                ];
            }),

            'hamahangi_date' => $this->hamahangi_date,
            'maktop_number'  => $this->maktop_number,
            'organ'          => $this->organ,
            'message_to_finance' => $this->message_to_finance,
            'money_amount_words' => $this->money_amount_words,
            'finance_status' => $this->finance_status ?? 'pending',
            'finance_paid_at' => optional($this->finance_paid_at)?->toISOString(),
            'finance_paid_amount' => $this->whenLoaded('finances', function () {
                return (float) $this->finances->sum(function ($f) {
                    return (float) ($f->money_amount_number ?? 0);
                });
            }),
            'finance_paid_amount_number' => $this->whenLoaded('finances', function () {
                $sum = (float) $this->finances->sum(function ($f) {
                    return (float) ($f->money_amount_number ?? 0);
                });
                return number_format($sum, 2, '.', '');
            }),

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
            'trust_percentage'       => $info ? $info->trust_percentage : null,

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