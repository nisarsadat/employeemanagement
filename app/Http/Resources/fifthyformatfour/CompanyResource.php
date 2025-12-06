<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray($request): array
    {
        // ✅ Safely get the related resource if it's loaded
        $related = $this->relationLoaded('resource')
            ? $this->getRelation('resource')
            : null;

        return [
            'id'                      => $this->id,

            'com_name'                => $this->com_name,
            'com_type'                => $this->com_type,
            'product_type'            => $this->product_type,
            'com_level'               => $this->com_level,
            'main_owner'              => $this->main_owner,

            'responsible_name'        => $this->responsible_name,
            'responsible_f_name'      => $this->responsible_f_name,
            'responsible_position'    => $this->responsible_position,
            'responsible_phone'       => $this->responsible_phone,

            'com_province'            => $this->com_province,
            'com_zone'                => $this->com_zone,
            'com_district'            => $this->com_district,
            'com_village'             => $this->com_village,
            'com_specific_place'      => $this->com_specific_place,
            'com_coordinates'         => $this->com_coordinates,

            'male_employee_quantity_inner'    => $this->male_employee_quantity_inner,
            'female_employee_quantity_inner'  => $this->female_employee_quantity_inner,
            'male_employee_quantity_out'      => $this->male_employee_quantity_out,
            'female_employee_quantity_out'    => $this->female_employee_quantity_out,
            'guard_quantity'                  => $this->guard_quantity,

            'note'                    => $this->note,
            'Province_addr'           => $this->Province_addr,

            // ✅ resource fields (flattened)
            'resource_id'             => $this->resource_id,
            'resource_code'           => $related?->code_number,
            'resource_phone'          => $related?->phone_number,
            'resource_phone_number'   => $related?->phone_number,

            // ✅ full minimal embedded resource when loaded
            'resource' => $this->when(
                $related !== null,
                fn () => [
                    'id'           => $related->id,
                    'name'         => $related->name,
                    'code_number'  => $related->code_number,
                    'phone_number' => $related->phone_number,
                ]
            ),

            'created_at'              => $this->created_at,
            'updated_at'              => $this->updated_at,
        ];
    }
}
