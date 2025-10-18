<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ZoneEmployeeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'picture'        => $this->picture,
            'name'           => $this->name,
            'father_name'    => $this->father_name,
            'nickname'       => $this->nickname,
            'code_number'    => $this->code_number,
            'hawza'          => $this->hawza,
            'zone'           => $this->zone,
            'degree'         => $this->degree,
            'zone_entry_hour'=> $this->zone_entry_hour,
            'resedint_id'    => $this->resedint_id,
            'notes'          => $this->notes,

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
