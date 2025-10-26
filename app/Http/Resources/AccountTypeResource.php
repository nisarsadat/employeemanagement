<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountTypeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'   => $this->id,
            'name' => $this->name,
            'note' => $this->note,
            // Optionally include count of accounts (when loaded)
            'accounts_count' => $this->when(isset($this->accounts_count), $this->accounts_count),
        ];
    }
}

