<?php

namespace App\Http\Resources\fifthyformatfour;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                                    => $this->id,

            'com_name'                              => $this->com_name,
            'com_type'                              => $this->com_type,
            'product_type'                          => $this->product_type,
            'com_level'                             => $this->com_level,
            'main_owner'                            => $this->main_owner,

            'responsible_name'                      => $this->responsible_name,
            'responsible_f_name'                    => $this->responsible_f_name,
            'responsible_position'                  => $this->responsible_position,
            'responsible_phone'                     => $this->responsible_phone,

            'com_province'                          => $this->com_province,
            'com_zone'                              => $this->com_zone,
            'com_district'                          => $this->com_district,
            'com_village'                           => $this->com_village,
            'com_specific_place'                    => $this->com_specific_place,
            'com_coordinates'                       => $this->com_coordinates,

            'male_employee_quantity_inner'          => $this->male_employee_quantity_inner,
            'female_employee_quantity_inner'        => $this->female_employee_quantity_inner,
            'male_employee_quantity_out'            => $this->male_employee_quantity_out,
            'female_employee_quantity_out'          => $this->female_employee_quantity_out,
            'guard_quantity'                        => $this->guard_quantity,

            // Flatten from related Resource
            'resource_code'                         => $this->resource->code_number ?? null,
            'resource_phone'                        => $this->resource->phone_number ?? null,

            'resource_id'                           => $this->resource_id,
            'resource'                              => $this->whenLoaded('resource', fn () => [
                'id'   => $this->resource->id,
                'name' => $this->resource->name ?? null,
            ]),

            'note'                                  => $this->note,
            'Province_addr'               => $this->Province_addr,

            'created_at'                            => $this->created_at,
            'updated_at'                            => $this->updated_at,
        ];
    }
}
