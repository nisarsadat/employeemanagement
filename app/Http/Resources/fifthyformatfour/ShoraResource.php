<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class ShoraResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                     => $this->id,

            'shora_village'          => $this->shora_village,
            'note'                   => $this->note,

            'shora_name'             => $this->shora_name,
            'shora_goal'             => $this->shora_goal,
            'people_quantity'        => $this->people_quantity,
            'week_meeting_quantity'  => $this->week_meeting_quantity,
            'regester'               => $this->regester,
            'shora_meeting_place'    => $this->shora_meeting_place,

            'responsible_name'       => $this->responsible_name,
            'responsible_f_name'     => $this->responsible_f_name,
            'responsible_job'        => $this->responsible_job,
            'responsible_phone'      => $this->responsible_phone,

            'supplier_name'          => $this->supplier_name,
            'supplier_job'           => $this->supplier_job,
            'supplier_phone'         => $this->supplier_phone,

            'emarat_tarfdary'        => $this->emarat_tarfdary,

            'shora_province'         => $this->shora_province,
            'shora_zone'             => $this->shora_zone,
            'shora_district'         => $this->shora_district,

            // Flatten from related Resource
            'resource_code'          => $this->resource->code_number ?? null,
            'resource_phone'         => $this->resource->phone_number ?? null,

            'resource_id'            => $this->resource_id,
            'resource'               => $this->whenLoaded('resource', fn () => [
                'id'   => $this->resource->id,
                'name' => $this->resource->name ?? null,
            ]),

            'created_at'             => $this->created_at,
            'updated_at'             => $this->updated_at,
        ];
    }
}
