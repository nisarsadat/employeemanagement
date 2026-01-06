<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KhayratReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'resource_id'        => $this->resource_id,
            'office_employee_id' => $this->office_employee_id,

            'zone'           => $this->zone,
            'hawza'          => $this->hawza,
            'specefic_place' => $this->specefic_place,
            'saved_date'     => optional($this->saved_date)->format('Y-m-d'),

            // ✅ flattened like your OfficeEmployee style
            'resource_name'  => $this->resource?->name,
            'resource_code'  => $this->resource?->code_number,
            'resource_phone' => $this->resource?->phone_number,

            'office_employee_name' => $this->officeEmployee?->name,
            'office_employee_code' => $this->officeEmployee?->code_number,

            // ✅ nested relations
            'resource' => $this->whenLoaded('resource', fn () => [
                'id'           => $this->resource->id,
                'name'         => $this->resource->name,
                'code_number'  => $this->resource->code_number,
                'phone_number' => $this->resource->phone_number,
            ]),

            'office_employee' => $this->whenLoaded('officeEmployee', fn () => [
                'id'          => $this->officeEmployee->id,
                'name'        => $this->officeEmployee->name,
                'code_number' => $this->officeEmployee->code_number,
            ]),
        ];
    }
}
