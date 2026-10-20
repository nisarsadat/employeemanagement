<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class OutgoingIncomingResource extends JsonResource
{
    /**
     * Safely format a date value.
     */
    private function formatDate($value, string $format = 'Y-m-d'): ?string
    {
        if (empty($value)) {
            return null;
        }

        return Carbon::parse($value)->format($format);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            // Document Info
            'docs_type' => $this->docs_type,

            // Office Outgoing
            'office_outgoing_date' => $this->formatDate($this->office_outgoing_date),
            'office_outgoing_page' => $this->office_outgoing_page,

            // Office Incoming
            'office_incoming_date' => $this->formatDate($this->office_incoming_date),
            'office_incoming_page' => $this->office_incoming_page,

            // Reyasat Outgoing
            'reyasat_outgoing_date' => $this->formatDate($this->reyasat_outgoing_date),
            'reyasat_outgoing_page' => $this->reyasat_outgoing_page,

            'reyasat_incomming_date' => $this->formatDate($this->reyasat_incomming_date),
            'reyasat_incomming_page' => $this->reyasat_incomming_page,

            // Loy Reyasat Outgoing
            'loy_reyasat_outgoing_date' => $this->formatDate($this->loy_reyasat_outgoing_date),
            'loy_reyasat_outgoing_page' => $this->loy_reyasat_outgoing_page,

            // Answer
            'answer_date' => $this->formatDate($this->answer_date),
            'answer_page' => $this->answer_page,

            // Sign & Final Dates
            'peshnehad' => $this->peshnehad,
            'outgoing_incoming_date' => $this->formatDate($this->outgoing_incoming_date),

            // Meta Info
            'who_sighn' => $this->who_sighn,
            'marja' => $this->marja,
            'tafseel' => $this->tafseel,

            // Archive Info
            'page' => $this->page,
            'dusya' => $this->dusya,
            'fihrist_page' => $this->fihrist_page,

            // Counts
            'naqal_count' => $this->naqal_count,
            'awraq' => $this->awraq,
            'zamima' => $this->zamima,

            // Notes
            'notes' => $this->notes,

            // Timestamps
            'created_at' => $this->formatDate($this->created_at, 'Y-m-d H:i'),
            'updated_at' => $this->formatDate($this->updated_at, 'Y-m-d H:i'),
        ];
    }
}
