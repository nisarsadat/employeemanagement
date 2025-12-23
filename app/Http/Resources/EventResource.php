<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
    public function toArray($request)
    {
        $resource           = $this->whenLoaded('resource');
        $officeEmployee     = $this->whenLoaded('officeEmployee');
        $akhistonkyEmployee = $this->whenLoaded('akhistonkyEmployee');
        $documents          = $this->whenLoaded('documents');

        return [
            'id'                   => $this->id,
            'event_type'           => $this->event_type,
            'event_specific_type'  => $this->event_specific_type,
            'target_person'        => $this->target_person,
            'criminal'             => $this->criminal,
            'event_date'           => $this->event_date,
            'event_hour'           => $this->event_hour,
            'event_zone'           => $this->event_zone,
            'event_hawza'          => $this->event_hawza,
            'event_specific_place' => $this->event_specific_place,
            'talafat_jani'         => $this->talafat_jani,
            'talafat_mali'         => $this->talafat_mali,
            'injured'              => $this->injured,
            'killed'               => $this->killed,
            'tafseel'              => $this->tafseel,
            'talks'                => $this->talks,
            'coordinates'          => $this->coordinates,
            'note'                 => $this->note,
            'docs_note'            => $this->docs_note,
            'Province_addr'        => $this->Province_addr,

            // Flattened FK / helper fields
            'resource_id'    => $this->resource_id,
            'resource_code'  => $resource ? ($resource->code_number ?? null) : null,
            'resource_phone' => $resource ? ($resource->phone_number ?? null) : null,

            'office_employee_id'    => $this->office_employee_id,
            'office_employee_code'  => $officeEmployee ? ($officeEmployee->code_number ?? null) : null,
            'office_employee_phone' => $officeEmployee ? ($officeEmployee->phone_number ?? null) : null,

            // ✅ Aghistonky: ID + code + phone
            'office_employee_akhistonky_id'    => $this->office_employee_akhistonky_id,
            'office_employee_akhistonky_code'  => $akhistonkyEmployee ? ($akhistonkyEmployee->code_number ?? null) : null,
            'office_employee_akhistonky_phone' => $akhistonkyEmployee ? ($akhistonkyEmployee->phone_number ?? null) : null,

            // Embedded resource
            'resource' => $this->when($resource, function () use ($resource) {
                return [
                    'id'           => $resource->id,
                    'name'         => $resource->name ?? $resource->full_name ?? null,
                    'father_name'  => $resource->father_name ?? null,
                    'code_number'  => $resource->code_number ?? null,
                    'phone_number' => $resource->phone_number ?? null,
                ];
            }),

            // Embedded office employee
            'office_employee' => $this->when($officeEmployee, function () use ($officeEmployee) {
                return [
                    'id'           => $officeEmployee->id,
                    'name'         => $officeEmployee->name ?? $officeEmployee->employee_name ?? null,
                    'father_name'  => $officeEmployee->father_name ?? null,
                    'code_number'  => $officeEmployee->code_number ?? null,
                    'phone_number' => $officeEmployee->phone_number ?? null,
                ];
            }),

            // Documents
            'documents' => $this->when($documents, function () use ($documents) {
                return $documents->map(function ($doc) {
                    return [
                        'id'         => $doc->id,
                        'file_name'  => $doc->file_name,
                        'file_path'  => $doc->file_path,
                        'mime_type'  => $doc->mime_type,
                        'size_bytes' => $doc->size_bytes,
                        'created_at' => $doc->created_at,
                    ];
                });
            }),
        ];
    }
}
