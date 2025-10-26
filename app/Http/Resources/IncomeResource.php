<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class IncomeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'amount'      => $this->amount,
            'received_at' => $this->received_at?->toDateString(),
            'note'        => $this->note,
            'date'        => $this->received_at,
            'account'     => $this->whenLoaded('account', fn() => [
                'id' => $this->account->id,
                'name' => $this->account->name,
                'price' => $this->account->price,
            ]),
        ];
    }
}

