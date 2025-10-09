<?php
// app/Http/Resources/InformationDocumentResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class InformationDocumentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'original_name' => $this->original_name,
            'mime'          => $this->mime,
            'size'          => $this->size,
            'url'           => $this->path ? Storage::disk('public')->url($this->path) : null,
        ];
    }
}
