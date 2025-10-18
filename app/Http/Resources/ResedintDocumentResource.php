<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ResedintDocumentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'file_name'     => $this->file_name,
            'file_path'     => $this->file_path,
            'url'           => $this->file_path ? Storage::disk('public')->url($this->file_path) : null,
            'mime_type'     => $this->mime_type,
            'size_bytes'    => $this->size_bytes,
            'created_at'    => $this->created_at,
        ];
    }
}
