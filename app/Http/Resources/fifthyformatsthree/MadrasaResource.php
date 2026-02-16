<?php

namespace App\Http\Resources\fifthyformatsthree;

use Illuminate\Http\Resources\Json\JsonResource;

class MadrasaResource extends JsonResource
{
    public function toArray($request): array
    {
        $res = $this->resource->getRelationValue('resource');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'madrasa_type' => $this->madrasa_type,

            'responsible_name' => $this->responsible_name,
            'responsible_f_name' => $this->responsible_f_name,
            'responsible_position' => $this->responsible_position,
            'responsible_phone' => $this->responsible_phone,
            'responsible_main_province' => $this->responsible_main_province,
            'tarafdari_type_responsible' => $this->tarafdari_type_responsible,

            'mazhab' => $this->mazhab,
            'male_teacher_quantity' => $this->male_teacher_quantity,
            'female_teacher_quantity' => $this->female_teacher_quantity,
            'male_students_quantity' => $this->male_students_quantity,
            'female_students_quantity' => $this->female_students_quantity,
            'picture' => $this->picture ? url($this->picture) : null,

            'province' => $this->province,
            'zone' => $this->zone,
            'district' => $this->district,
            'village' => $this->village,
            'specific_place' => $this->specific_place,
            'street_number' => $this->street_number,
            'coordinates' => $this->coordinates,
            'Province_addr'               => $this->Province_addr,

            'note' => $this->note,
            'resource_id' => $this->resource_id,
            'resource_code' => $res?->code_number,
            'resource_phone' => $res?->phone_number,

            'resource' => $this->whenLoaded('resource', function () use ($res) {
                return $res ? [
                    'id' => $res->id,
                    'name' => $res->name,
                    'code_number' => $res->code_number,
                    'phone_number' => $res->phone_number,
                ] : null;
            }),
        ];
    }
}
