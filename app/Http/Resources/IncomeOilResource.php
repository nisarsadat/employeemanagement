<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class IncomeOilResource extends JsonResource
{
        public function toArray($request): array {
        return [
            'id'               => $this->id,
            'oil_account_id'   => $this->oil_account_id,
            'account_name'     => $this->account?->name,
            'account_quantity' => $this->account?->quantity !== null ? (float)$this->account->quantity : null,

            'date'         => optional($this->date)->toDateString(), // <-- NEW
            'oil_quantity' => (float) $this->oil_quantity,
            'source'       => $this->source,
            'note'         => $this->note,
            'created_at'   => $this->created_at,

            'account' => $this->whenLoaded('account', fn() => [
                'id' => $this->account->id,
                'name' => $this->account->name,
                'quantity' => (float) $this->account->quantity,
            ]),
        ];
    }

}
