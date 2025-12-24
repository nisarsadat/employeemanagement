<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncomeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $account = $this->getRelationValue('account');

        return [
            'id' => $this->id,
            'account_id' => $this->account_id,
            'amount' => $this->amount,
            'received_at' => optional($this->received_at)->toDateString(),
            'note' => $this->note,

            'account_name' => $account?->name,

            'account' => $this->whenLoaded('account', function () use ($account) {
                return $account ? [
                    'id' => $account->id,
                    'name' => $account->name,
                    'price' => $account->price,
                ] : null;
            }),

            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
