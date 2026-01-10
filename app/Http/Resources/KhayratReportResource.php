<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KhayratReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // ✅ IMPORTANT: "resource" relationship conflicts with JsonResource::$resource
        $resourceRel = $this->resource->getRelationValue('resource');
        $officeRel   = $this->resource->getRelationValue('officeEmployee');

        return [
            'id' => $this->id,

            'resource_id'        => $this->resource_id,
            'office_employee_id' => $this->office_employee_id,

            'zone'           => $this->zone,
            'hawza'          => $this->hawza,
            'specefic_place' => $this->specefic_place,
            'saved_date'     => optional($this->saved_date)->format('Y-m-d'),

            // ✅ flattened
            'resource_name'  => $resourceRel?->name,
            'resource_code'  => $resourceRel?->code_number,
            'resource_phone' => $resourceRel?->phone_number,

            'office_employee_name' => $officeRel?->name,
            'office_employee_code' => $officeRel?->code_number,

            // ✅ nested relations
            'resource' => $this->whenLoaded('resource', fn () => [
                'id'           => $resourceRel?->id,
                'name'         => $resourceRel?->name,
                'code_number'  => $resourceRel?->code_number,
                'phone_number' => $resourceRel?->phone_number,
            ]),

            'office_employee' => $this->whenLoaded('officeEmployee', fn () => [
                'id'          => $officeRel?->id,
                'name'        => $officeRel?->name,
                'code_number' => $officeRel?->code_number,
            ]),
        ];
    }
}
