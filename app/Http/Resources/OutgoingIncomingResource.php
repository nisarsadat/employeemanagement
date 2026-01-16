<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutgoingIncomingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'docs_type' => $this->docs_type,

            'office_outgoing_date' => $this->office_outgoing_date,
            'office_outgoing_page' => $this->office_outgoing_page,

            'office_incoming_date' => $this->office_incoming_date,
            'office_incoming_page' => $this->office_incoming_page,

            'reyasat_outgoing_date' => $this->reyasat_outgoing_date,
            'reyasat_outgoing_page' => $this->reyasat_outgoing_page,

            'loy_reyasat_outgoing_date' => $this->loy_reyasat_outgoing_date,
            'loy_reyasat_outgoing_page' => $this->loy_reyasat_outgoing_page,

            'answer_date' => $this->answer_date,
            'answer_page' => $this->answer_page,

            'docs_sign_date' => $this->docs_sign_date,
            'outgoing_incoming_date' => $this->outgoing_incoming_date,

            'who_sighn' => $this->who_sighn,
            'marja' => $this->marja,
            'tafseel' => $this->tafseel,

            'page' => $this->page,
            'dusya' => $this->dusya,
            'fihrist_page' => $this->fihrist_page,

            'naqal_count' => $this->naqal_count,
            'awraq' => $this->awraq,
            'zamima' => $this->zamima,

            'notes' => $this->notes,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
