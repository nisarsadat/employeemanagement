<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CaptiveDeliveryDocumentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'captive_delivery_id' => $this->captive_delivery_id,
            'file_name'  => $this->file_name,
            'file_path'  => $this->file_path,
            'mime_type'  => $this->mime_type,
            'size_bytes' => $this->size_bytes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
