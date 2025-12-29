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

            // ========================
            // Incoming / Outgoing
            // ========================
            'outgoing_to_reyasat' => $this->outgoing_to_reyasat,
            'out_going_to_others' => $this->out_going_to_others,
            'incomming' => $this->incomming,

            // ========================
            // Ahkam
            // ========================
            'ahkam_reyasat' => $this->ahkam_reyasat,
            'ahkam_rad_shoda_reyasat' => $this->ahkam_rad_shoda_reyasat,
            'reyasat_na_direction' => $this->reyasat_na_direction,

            'loy_reyasat_ahkam' => $this->loy_reyasat_ahkam,
            'loy_reyasat_ahakm_rad' => $this->loy_reyasat_ahakm_rad,
            'loy_reyasat_direction' => $this->loy_reyasat_direction,

            // ========================
            // Numbers & Dates
            // ========================
            'number' => $this->number,

            // ✅ dates are casted in model → already YYYY-MM-DD
            'sign_date' => $this->sign_date,
            'outgoing_incoming_date' => $this->outgoing_incoming_date,

            // ========================
            // Reference
            // ========================
            'marja' => $this->marja,
            'who_sign' => $this->who_sign,

            'numer_of_jold' => $this->numer_of_jold,
            'list_number' => $this->list_number,

            // ========================
            // Content
            // ========================
            'naql_amount' => $this->naql_amount,
            'pages' => $this->pages,
            'zamima' => $this->zamima,
            'notes' => $this->notes,

            // ========================
            // Documents
            // ========================
            'sundry_documents' => SundryDocumentResource::collection(
                $this->whenLoaded('sundryDocuments')
            ),

            // ========================
            // Meta
            // ========================
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
