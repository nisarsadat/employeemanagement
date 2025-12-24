<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $type = $this->getRelationValue('accountType');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'account_type_id' => $this->account_type_id,
            'details' => $this->details,

            'account_type_name' => $type?->name,

            'account_type' => $this->whenLoaded('accountType', function () use ($type) {
                return $type ? [
                    'id' => $type->id,
                    'name' => $type->name,
                ] : null;
            }),

            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
