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

            // =====================
            // Document Info
            // =====================
            'docs_type' => $this->docs_type,

            // =====================
            // Office Outgoing
            // =====================
            'office_outgoing_date' => $this->office_outgoing_date
                ? $this->office_outgoing_date->format('Y-m-d')
                : null,
            'office_outgoing_page' => $this->office_outgoing_page,

            // =====================
            // Office Incoming
            // =====================
            'office_incoming_date' => $this->office_incoming_date
                ? $this->office_incoming_date->format('Y-m-d')
                : null,
            'office_incoming_page' => $this->office_incoming_page,

            // =====================
            // Reyasat Outgoing
            // =====================
            'reyasat_outgoing_date' => $this->reyasat_outgoing_date
                ? $this->reyasat_outgoing_date->format('Y-m-d')
                : null,
            'reyasat_outgoing_page' => $this->reyasat_outgoing_page,

            'reyasat_incomming_date' => $this->reyasat_incomming_date
                ? $this->reyasat_incomming_date->format('Y-m-d')
                : null,
            'reyasat_incomming_page' => $this->reyasat_incomming_page,

            // =====================
            // Loy Reyasat Outgoing
            // =====================
            'loy_reyasat_outgoing_date' => $this->loy_reyasat_outgoing_date
                ? $this->loy_reyasat_outgoing_date->format('Y-m-d')
                : null,
            'loy_reyasat_outgoing_page' => $this->loy_reyasat_outgoing_page,

            // =====================
            // Answer
            // =====================
            'answer_date' => $this->answer_date
                ? $this->answer_date->format('Y-m-d')
                : null,
            'answer_page' => $this->answer_page,

            // =====================
            // Sign & Final Dates
            // =====================
            'peshnehad' => $this->peshnehad,
            'outgoing_incoming_date' => $this->outgoing_incoming_date
                ? $this->outgoing_incoming_date->format('Y-m-d')
                : null,

            // =====================
            // Meta Info
            // =====================
            'who_sighn' => $this->who_sighn,
            'marja'     => $this->marja,
            'tafseel'   => $this->tafseel,

            // =====================
            // Archive Info
            // =====================
            'page'         => $this->page,
            'dusya'        => $this->dusya,
            'fihrist_page' => $this->fihrist_page,

            // =====================
            // Counts
            // =====================
            'naqal_count' => $this->naqal_count,
            'awraq'       => $this->awraq,
            'zamima'      => $this->zamima,

            // =====================
            // Notes
            // =====================
            'notes' => $this->notes,

            // =====================
            // Timestamps
            // =====================
            'created_at' => $this->created_at
                ? $this->created_at->format('Y-m-d H:i')
                : null,
            'updated_at' => $this->updated_at
                ? $this->updated_at->format('Y-m-d H:i')
                : null,
        ];
    }
}
