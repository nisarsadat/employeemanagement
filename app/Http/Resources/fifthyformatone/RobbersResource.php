<?php 
namespace App\Http\Resources\fifthyformatone;

use Illuminate\Http\Resources\Json\JsonResource;

class RobbersResource extends JsonResource
{
    public function toArray($request): array
    {
        $model = $this->resource;
        $res   = $model->getRelationValue('resource'); // null if not loaded

        return [
            'id'                  => $model->id,
            'nickname'            => $model->nickname,
            'name'                => $model->name,
            'father_name'         => $model->father_name,

            'main_province'       => $model->main_province,
            'main_district'       => $model->main_district,
            'main_village'        => $model->main_village,

            'now_province'        => $model->now_province,
            'now_zone'            => $model->now_zone,
            'now_district'        => $model->now_district,
            'now_village'         => $model->now_village,
            'now_specific_place'  => $model->now_specific_place,
            'now_street_number'   => $model->now_street_number,
            'now_home_number'     => $model->now_home_number,
            'Province_addr'               => $this->Province_addr,

            'old_job'             => $model->old_job,
            'now_job'             => $model->now_job,
            'supplier'            => $model->supplier,
            'phone_number'        => $model->phone_number,
            'history_form'        => $model->history_form,
            'coordinates'        => $model->coordinates,

            'note'                => $model->note,
            'friends_quantity'    => $model->friends_quantity,

            // FK (for saving)
            'resource_id'         => $model->resource_id,

            // Derived flat fields from related resource row
            'resource_code'       => $res->code_number  ?? null,
            'resource_phone'      => $res->phone_number ?? null,

            // Minimal embed of the relation
            'resource'            => $this->whenLoaded('resource', function () use ($res) {
                return [
                    'id'   => $res->id   ?? null,
                    'name' => $res->name ?? null,
                ];
            }),

            'created_at'          => $model->created_at,
            'updated_at'          => $model->updated_at,
        ];
    }
}
