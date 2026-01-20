<?php 
namespace App\Http\Resources\fifthyformatone;

use Illuminate\Http\Resources\Json\JsonResource;

class MashkokHomesResource extends JsonResource
{
    public function toArray($request): array
    {
        $res = $this->resource->getRelationValue('resource'); // safe accessor

        return [
            'id'             => $this->id,
            'province'       => $this->province,
            'zone'           => $this->zone,
            'district'       => $this->district,
            'village'        => $this->village,
            'specific_place' => $this->specific_place,
            'street_number'  => $this->street_number,
            'home_number'    => $this->home_number,
            'coordinates'    => $this->coordinates,
            'home_type'      => $this->home_type,
            'shak_symbols'   => $this->shak_symbols,
            'note'           => $this->note,
            'Province_addr'               => $this->Province_addr,

            'resource_id'    => $this->resource_id,

            // flat, derived fields (what your frontend expects)
            'resource_code'  => $res->code_number  ?? null,
            'resource_phone' => $res->phone_number ?? null,

            // embed minimal related resource (already loaded)
            'resource' => $this->whenLoaded('resource', function () use ($res) {
                return [
                    'id'           => $res->id ?? null,
                    'name'         => $res->name ?? null,
                    'code_number'  => $res->code_number ?? null,
                    'phone_number' => $res->phone_number ?? null,
                ];
            }),
            'documents' => $this->whenLoaded('documents', function () {
                return $this->documents->map(function ($doc) {
                    return [
                        'id'            => $doc->id,
                        'file_path'     => $doc->file_path,
                        'original_name' => $doc->original_name,
                        'mime_type'     => $doc->mime_type,
                        'size'          => $doc->size,
                        'created_at'    => $doc->created_at,
                    ];
                });
            }),


            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
