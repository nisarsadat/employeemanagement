<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

// Main Fifty Formats
use App\Models\fifthyFormats\Fasad;
use App\Models\fifthyFormats\SaeenFasad;
use App\Models\fifthyFormats\Heist;
use App\Models\fifthyFormats\Captive;
use App\Models\fifthyFormats\Mulkano;
use App\Models\fifthyFormats\EntryExit;
use App\Models\fifthyFormats\Sarafyano;
use App\Models\fifthyFormats\Clubs;
use App\Models\fifthyFormats\Mahajerat;
use App\Models\fifthyFormats\Soldier;

// Fifthy Format One
use App\Models\fifthyformatone\Mawad;
use App\Models\fifthyformatone\LostMujremeen;
use App\Models\fifthyformatone\OldSoldiersHomes;
use App\Models\fifthyformatone\Robbers;
use App\Models\fifthyformatone\NonResponsablePeople;
use App\Models\fifthyformatone\MashkokHomes;
use App\Models\fifthyformatone\DamagePlaces;
use App\Models\fifthyformatone\DamageRasanPlaces;
use App\Models\fifthyformatone\MashkokShops;
use App\Models\fifthyformatone\Luchakano;

// Fifthy Format Two
use App\Models\fifthyformatstow\School;
use App\Models\fifthyformatstow\HadoBusStop;
use App\Models\fifthyformatstow\TransportCompany;
use App\Models\fifthyformatstow\CarSalesCompany;
use App\Models\fifthyformatstow\Parking;
use App\Models\fifthyformatstow\Bank;
use App\Models\fifthyformatstow\Apartment;
use App\Models\fifthyformatstow\RahnomaMamlat;
use App\Models\fifthyformatstow\Hosppital;
use App\Models\fifthyformatstow\WorkShop;

// Fifthy Format Three
use App\Models\fifthyformatsthree\Badmashano;
use App\Models\fifthyformatsthree\EmaratOffice;
use App\Models\fifthyformatsthree\Hostel;
use App\Models\fifthyformatsthree\Hotel;
use App\Models\fifthyformatsthree\Madrasa;
use App\Models\fifthyformatsthree\MashkokPeople;
use App\Models\fifthyformatsthree\Mujahedino;
use App\Models\fifthyformatsthree\OldJahadi;
use App\Models\fifthyformatsthree\Sarayanos;
use App\Models\fifthyformatsthree\Tujaran;

// Fifthy Format Four
use App\Models\fifthyformatfour\Checkpoint;
use App\Models\fifthyformatfour\Masjid;
use App\Models\fifthyformatfour\Highway;
use App\Models\fifthyformatfour\Company;
use App\Models\fifthyformatfour\Donelostplace;
use App\Models\fifthyformatfour\EconemyProject;
use App\Models\fifthyformatfour\CriminalCapturePlace;
use App\Models\fifthyformatfour\Pump;
use App\Models\fifthyformatfour\Shora;
use App\Models\fifthyformatfour\GovernmentPlace;
use App\Models\fifthyformatfour\SuperMarket;
use App\Models\fifthyformatfour\Park;
use App\Models\fifthyformatfour\Complex;
use App\Models\fifthyformatfour\DeplomaticPlace;
use App\Models\fifthyformatfour\University;
use App\Models\fifthyformatfour\Wakilano;
use App\Models\fifthyformatsthree\Sarayano;

class FifthyFormatsCountController extends Controller
{
    /**
     * Return counts for all 56 formats as a flat key → count object.
     * Keys MUST match the `slug` in your Vue `original` array.
     */
    public function index(): JsonResponse
    {
        $map = [
            // Main Fifty Formats (10)
            'fasads'               => Fasad::class,
            'saeenfasad'         => SaeenFasad::class,
            'heist'               => Heist::class,
            'captives'             => Captive::class,
            'mulkanos'             => Mulkano::class,
            'entry-exits'          => EntryExit::class,
            'sarafyanos'           => Sarafyano::class,
            'clubs'                => Clubs::class,
            'mahajerats'           => Mahajerat::class,
            'soldiers'             => Soldier::class,

            // Fifthy Format One (10)
            'mawads'               => Mawad::class,
            'lost-mujremeens'      => LostMujremeen::class,
            'old-soldiers-homes'   => OldSoldiersHomes::class,
            'robbers'              => Robbers::class,
            'non-responsable-people' => NonResponsablePeople::class,
            'mashkok-homes'        => MashkokHomes::class,
            'damage-places'        => DamagePlaces::class,
            'damage-rasan-places'  => DamageRasanPlaces::class,
            'mashkok-shops'        => MashkokShops::class,
            'luchakanos'           => Luchakano::class,

            // Fifthy Format Two (10)
            'schools'              => School::class,
            'hado-bus-stops'       => HadoBusStop::class,
            'transport-companies'  => TransportCompany::class,
            'car-sales-companies'  => CarSalesCompany::class,
            'parkings'             => Parking::class,
            'banks'                => Bank::class,
            'apartments'           => Apartment::class,
            'rahnoma-mamlats'      => RahnomaMamlat::class,
            'hosppitals'           => Hosppital::class,
            'work-shops'           => WorkShop::class,

            // Fifthy Format Three (10)
            'badmashanos'          => Badmashano::class,
            'emarat-offices'       => EmaratOffice::class,
            'hostels'              => Hostel::class,
            'hotels'               => Hotel::class,
            'madrasas'             => Madrasa::class,
            'mashkok-people'       => MashkokPeople::class,
            'mujahedinos'          => Mujahedino::class,
            'old-jahadis'          => OldJahadi::class,
            'sarayanos'            => Sarayano::class,
            'tujarans'             => Tujaran::class,

            // Fifthy Format Four (16)
            'checkpoints'          => Checkpoint::class,
            'masjids'              => Masjid::class,
            'highways'             => Highway::class,
            'companies'            => Company::class,
            'done-lost-places'     => Donelostplace::class,
            'econemy-projects'     => EconemyProject::class,
            'criminal-capture-places' => CriminalCapturePlace::class,
            'pumps'                => Pump::class,
            'shoras'               => Shora::class,
            'governmentplaces'     => GovernmentPlace::class,
            'supermarkets'         => SuperMarket::class,
            'parks'                => Park::class,
            'complexes'            => Complex::class,
            'deplomaticplaces'     => DeplomaticPlace::class,
            'universities'         => University::class,
            'wakilanos'            => Wakilano::class,
        ];

        $result = [];

        foreach ($map as $slug => $modelClass) {
            $result[$slug] = $this->safeModelCount($modelClass);
        }

        return response()->json($result);
    }

    protected function safeModelCount(string $modelClass): int
    {
        try {
            return $modelClass::query()->count();
        } catch (\Throwable $e) {
            \Log::error('FifthyFormatsCount error for '.$modelClass.': '.$e->getMessage());
            return 0;
        }
    }
}
