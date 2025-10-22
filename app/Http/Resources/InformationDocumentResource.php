<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InformationDocumentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'file_name'  => $this->file_name,
            'file_path'  => asset('storage/'.$this->file_path),
            'mime_type'  => $this->mime_type,
            'size_bytes' => $this->size_bytes,
        ];
    }
}
