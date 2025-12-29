<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SundryDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'outgoing_incoming_id' => $this->outgoing_incoming_id,
            'file_name' => $this->file_name,
            'file_path' => $this->file_path,
            'url' => asset('storage/' . $this->file_path),
            'file_size' => $this->file_size,
            'mime_type' => $this->mime_type,
            'created_at' => $this->created_at,
        ];
    }
}
