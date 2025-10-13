<?php
namespace App\Http\Resources\fifthyformatone;

use Illuminate\Http\Resources\Json\JsonResource;

class MawadResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var \App\Models\fifthyformatone\Mawad $model */
        $model = $this->resource; // the underlying Eloquent model

        return [
            'id'                => $model->id,
            'mawad_type'        => $model->mawad_type,
            'nickname'          => $model->nickname,
            'name'              => $model->name,
            'father_name'       => $model->father_name,

            // Main address
            'main_province'     => $model->main_province,
            'main_district'     => $model->main_district,
            'main_village'      => $model->main_village,

            // Current address
            'now_province'      => $model->now_province,
            'now_district'      => $model->now_district,
            'now_village'       => $model->now_village,

            // Extra location
            'province'          => $model->province,
            'zone'              => $model->zone,
            'district'          => $model->district,
            'village'           => $model->village,

            // Info
            'specific_place'    => $model->specific_place,
            'coordinates'       => $model->coordinates,
            'mawad_sales_phone' => $model->mawad_sales_phone,

            'history_form'      => $model->history_form,
            'supplier'          => $model->supplier,
            'note'              => $model->note,

            // Flattened from relation (match Fasad style)
            'resource_code'     => optional($model->resource)->code_number,
            'resource_phone'    => optional($model->resource)->phone_number,

            'resource_id'       => $model->resource_id,

            // Keep nested (match Fasad style)
            'resource'          => $this->whenLoaded('resource', function () use ($model) {
                return [
                    'id'   => optional($model->resource)->id,
                    'name' => optional($model->resource)->name,
                ];
            }),

            'created_at'        => $model->created_at,
            'updated_at'        => $model->updated_at,
        ];
    }
}
