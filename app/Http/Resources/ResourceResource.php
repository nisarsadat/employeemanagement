<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ResourceResource extends JsonResource
{
    private function relCount(string $attr, string $relation): int
    {
        // If controller used withCount, use it (fast)
        if (isset($this->{$attr})) return (int) $this->{$attr};

        // Fallback to counting relation
        return (int) $this->{$relation}()->count();
    }

    public function toArray($request): array
    {
        // ✅ Prefer precomputed value if present
        $fiftySixFormatsCount = isset($this->fifty_six_formats_count)
            ? (int) $this->fifty_six_formats_count
            : (
                  $this->fasads()->count()
                + $this->saeenFasads()->count()
                + $this->heists()->count()
                + $this->captives()->count()
                + $this->mulkanos()->count()
                + $this->entryExits()->count()
                + $this->sarafyanos()->count()
                + $this->clubs()->count()
                + $this->mahajerats()->count()
                + $this->soldiers()->count()
                + $this->mawads()->count()
                + $this->lostmujremeens()->count()
                + $this->oldSoldiersHomes()->count()
                + $this->robbers()->count()
                + $this->nonResponsablePeople()->count()
                + $this->mashkokHomes()->count()
                + $this->damagePlaces()->count()
                + $this->damageRasanPlaces()->count()
                + $this->mashkokShops()->count()
                + $this->luchakano()->count()
                + $this->workshops()->count()
                + $this->hosppitals()->count()
                + $this->rahnomaMamlats()->count()
                + $this->apartments()->count()
                + $this->banks()->count()
                + $this->transportCompanies()->count()
                + $this->hadoBusStops()->count()
                + $this->carSalesCompanies()->count()
                + $this->schools()->count()
                + $this->parkings()->count()
                + $this->wakilanos()->count()
                + $this->universities()->count()
                + $this->masjids()->count()
                + $this->highways()->count()
                + $this->companies()->count()
                + $this->donelostplaces()->count()
                + $this->econemyProjects()->count()
                + $this->criminalCapturePlaces()->count()
                + $this->pumps()->count()
                + $this->shoras()->count()
                + $this->checkpoints()->count()
                + $this->governmentplaces()->count()
                + $this->supermarkets()->count()
                + $this->parks()->count()
                + $this->complexes()->count()
                + $this->deplomaticplaces()->count()
                + $this->badmashanos()->count()
                + $this->emaratOffices()->count()
                + $this->hostels()->count()
                + $this->hotels()->count()
                + $this->madrasas()->count()
                + $this->mashkokPeople()->count()
                + $this->mujahedinos()->count()
                + $this->oldJahadis()->count()
                + $this->sarayanos()->count()
                + $this->tujarans()->count()
            );

        return [
            'id'       => $this->id,
            'picture'  => $this->picture ? url($this->picture) : null,

            // intro
            'name'               => $this->name,
            'father_name'        => $this->father_name,
            'grand_father_name'  => $this->grand_father_name,
            'nick_name'          => $this->nick_name,
            'code_number'        => $this->code_number,
            'tazkira'            => $this->tazkira,

            // main address
            'main_province'      => $this->main_province,
            'main_district'      => $this->main_district,
            'main_village'       => $this->main_village,

            // current address
            'now_province'       => $this->now_province,
            'now_district'       => $this->now_district,
            'now_village'        => $this->now_village,
            'now_specific_place' => $this->now_specific_place,
            'now_street_number'  => $this->now_street_number,
            'now_home_number'    => $this->now_home_number,

            // jobs
            'old_job'            => $this->old_job,
            'now_job'            => $this->now_job,
            'job_place'          => $this->job_place,

            // status & education
            'activity'           => $this->activity,
            'halat_madani'       => $this->halat_madani,
            'age'                => $this->age,
            'elmi_level'         => $this->elmi_level,
            'faculty'            => $this->faculty,
            'specific_skills'    => $this->specific_skills,
            'train'              => $this->train,

            // misc
            'resource_saved_date' => $this->resource_saved_date
                ? $this->resource_saved_date->format('Y-m-d')
                : null,
            'resource_activity_type' => $this->resource_activity_type,
            'office_employee_id'     => $this->office_employee_id,
            'resource_cover'         => $this->resource_cover,
            'language'               => $this->language,
            'nation'                 => $this->nation,
            'phone_number'           => $this->phone_number,
            'phone_number_1'         => $this->phone_number_1,
            'phone_number_2'         => $this->phone_number_2,
            'resedint_code'          => $this->resedint_code,
            'coordinates'            => $this->coordinates,
            'note'                   => $this->note,
            'Province_addr'          => $this->Province_addr,

            // ✅ Counts
            'events_count'            => $this->relCount('events_count', 'events'),
            'informations_count'      => $this->relCount('informations_count', 'informations'),
            'fifty_six_formats_count' => $fiftySixFormatsCount,

            // embedded office employee (minimal)
            'office_employee' => $this->whenLoaded('officeEmployee', function () {
                return [
                    'id'          => $this->officeEmployee->id,
                    'name'        => $this->officeEmployee->name,
                    'code_number' => $this->officeEmployee->code_number,
                    'father_name' => $this->officeEmployee->father_name,
                    'nick_name'   => $this->officeEmployee->nickname,
                    'phone_number'=> $this->officeEmployee->phone_number ?? null,
                ];
            }),
        ];
    }
}
