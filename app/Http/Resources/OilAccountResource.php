<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OilAccountResource extends JsonResource
{
    public function toArray($request): array {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'quantity' => (float) $this->quantity,
            'details'  => $this->details,
            'created_at' => $this->created_at,
        ];
    }
}
