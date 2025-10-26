<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'price'            => $this->price,
            'account_type_id'  => $this->account_type_id,
            'details'          => $this->details,
            // minimal embedded account_type
            'account_type'     => $this->whenLoaded('accountType', fn() => [
                'id'   => $this->accountType->id,
                'name' => $this->accountType->name,
            ]),
        ];
    }
}

