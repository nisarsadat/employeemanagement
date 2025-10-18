<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class OfficeEmployeeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'father_name'   => $this->father_name,
            'nickname'      => $this->nickname,
            'main_province' => $this->main_province,
            'main_district' => $this->main_district,
            'main_village'  => $this->main_village,
            'now_province'  => $this->now_province,
            'now_district'  => $this->now_district,
            'now_village'   => $this->now_village,
            'releg_study'   => $this->releg_study,
            'new_study'     => $this->new_study,
            'phone_number'  => $this->phone_number,
            'tazkira'       => $this->tazkira,
            'picture'       => $this->picture,
            'apply_date'    => $this->apply_date,
            'join_date'     => $this->join_date,
            'left_date'     => $this->left_date,
            'code_number'   => $this->code_number,
            'notes'         => $this->notes,

            'documents' => $this->whenLoaded('documents', function () {
                return $this->documents->map(function ($d) {
                    return [
                        'id'         => $d->id,
                        'file_name'  => $d->file_name,
                        'mime_type'  => $d->mime_type,
                        'size_bytes' => $d->size_bytes,
                        'file_path'  => $d->file_path,
                        'url'        => Storage::disk('public')->url($d->file_path),
                    ];
                });
            }),
        ];
    }
}
