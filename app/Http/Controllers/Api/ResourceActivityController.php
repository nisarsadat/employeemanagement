<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResourceResource;
use App\Models\Resource;
use Illuminate\Http\Request;

class ResourceActivityController extends Controller
{
    /**
     * What counts as "activity"?
     * - Any Event in last 1 month
     * - OR any Information in last 1 month
     * - OR any record in any of the 56-format relations in last 1 month
     */
    private function applyActiveScope($query, $since)
    {
        $formatRelations = [
            'fasads','saeenFasads','heists','captives','mulkanos','entryExits','sarafyanos','clubs',
            'mahajerats','soldiers','mawads','lostmujremeens','oldSoldiersHomes','robbers',
            'nonResponsablePeople','mashkokHomes','damagePlaces','damageRasanPlaces','mashkokShops',
            'luchakano','workshops','hosppitals','rahnomaMamlats','apartments','banks',
            'transportCompanies','hadoBusStops','carSalesCompanies','schools','parkings',
            'wakilanos','universities','masjids','highways','companies','donelostplaces',
            'econemyProjects','criminalCapturePlaces','pumps','shoras','checkpoints',
            'governmentplaces','supermarkets','parks','complexes','deplomaticplaces',
            'badmashanos','emaratOffices','hostels','hotels','madrasas','mashkokPeople',
            'mujahedinos','oldJahadis','sarayanos','tujarans',
        ];

        return $query->where(function ($q) use ($since, $formatRelations) {

            // ✅ EVENTS
            $q->whereHas('events', function ($qq) use ($since) {
                $qq->where('created_at', '>=', $since);
            });

            // ✅ INFORMATIONS
            $q->orWhereHas('informations', function ($qq) use ($since) {
                $qq->where('created_at', '>=', $since);
            });

            // ✅ ANY 56 FORMAT
            foreach ($formatRelations as $rel) {
                $q->orWhereHas($rel, function ($qq) use ($since) {
                    $qq->where('created_at', '>=', $since);
                });
            }
        });
    }

    private function applySearch($query, ?string $search)
    {
        $s = trim((string) $search);
        if ($s === '') return;

        $query->where(function ($q) use ($s) {
            $q->where('name', 'like', "%{$s}%")
              ->orWhere('code_number', 'like', "%{$s}%")
              ->orWhere('phone_number', 'like', "%{$s}%");
        });
    }

    private function withCounts($query)
    {
        $query->withCount(['events', 'informations']);

        $query->withCount([
            'fasads','saeenFasads','heists','captives','mulkanos','entryExits','sarafyanos','clubs','mahajerats','soldiers',
            'mawads','lostmujremeens','oldSoldiersHomes','robbers','nonResponsablePeople','mashkokHomes','damagePlaces','damageRasanPlaces','mashkokShops','luchakano',
            'workshops','hosppitals','rahnomaMamlats','apartments','banks','transportCompanies','hadoBusStops','carSalesCompanies','schools','parkings',
            'wakilanos','universities','masjids','highways','companies','donelostplaces','econemyProjects','criminalCapturePlaces','pumps','shoras','checkpoints',
            'governmentplaces','supermarkets','parks','complexes','deplomaticplaces',
            'badmashanos','emaratOffices','hostels','hotels','madrasas','mashkokPeople','mujahedinos','oldJahadis','sarayanos','tujarans',
        ]);

        return $query;
    }

    private function mapFormatSum($row): int
    {
        $keys = [
            'fasads_count','saeen_fasads_count','heists_count','captives_count','mulkanos_count','entry_exits_count','sarafyanos_count','clubs_count','mahajerats_count','soldiers_count',
            'mawads_count','lostmujremeens_count','old_soldiers_homes_count','robbers_count','non_responsable_people_count','mashkok_homes_count','damage_places_count',
            'damage_rasan_places_count','mashkok_shops_count','luchakano_count',
            'workshops_count','hosppitals_count','rahnoma_mamlats_count','apartments_count','banks_count','transport_companies_count','hado_bus_stops_count',
            'car_sales_companies_count','schools_count','parkings_count',
            'wakilanos_count','universities_count','masjids_count','highways_count','companies_count','donelostplaces_count','econemy_projects_count','criminal_capture_places_count',
            'pumps_count','shoras_count','checkpoints_count','governmentplaces_count','supermarkets_count','parks_count','complexes_count','deplomaticplaces_count',
            'badmashanos_count','emarat_offices_count','hostels_count','hotels_count','madrasas_count','mashkok_people_count','mujahedinos_count','old_jahadis_count','sarayanos_count','tujarans_count',
        ];

        $sum = 0;
        foreach ($keys as $k) $sum += (int)($row->{$k} ?? 0);
        return $sum;
    }

    public function counts(Request $request)
    {
        // ✅ last 1 month (instead of 7 days)
        $since = now()->subMonth();

        $base = Resource::query();
        $total = (clone $base)->count();

        $activeQ = Resource::query();
        $this->applyActiveScope($activeQ, $since);
        $active = $activeQ->count();

        $inactive = $total - $active;

        $today = now()->startOfDay();
        $todayAdded = Resource::query()
            ->where(function ($q) use ($today) {
                $q->whereDate('resource_saved_date', $today)
                  ->orWhereDate('created_at', $today);
            })
            ->count();

        return response()->json([
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'today_added' => $todayAdded,
            'since' => $since->toDateTimeString(),
        ]);
    }

    public function index(Request $request)
    {
        $mode = $request->get('mode', 'active'); // active | inactive
        $perPage = $request->get('perPage', 25); // 10/25/50 or "all"
        $search = $request->get('search', null);

        // ✅ last 1 month (instead of 7 days)
        $since = now()->subMonth();

        $q = Resource::query()
            ->with(['officeEmployee:id,name,code_number,phone_number,father_name,nick_name'])
            ->orderByDesc('id');

        $this->withCounts($q);
        $this->applySearch($q, $search);

        if ($mode === 'active') {
            $this->applyActiveScope($q, $since);
        } else {
            $activeSub = Resource::query()->select('id');
            $this->applyActiveScope($activeSub, $since);
            $q->whereNotIn('id', $activeSub);
        }

        // "all" = no pagination
        if ($perPage === 'all') {
            $items = $q->get()->map(function ($r) {
                $r->fifty_six_formats_count = $this->mapFormatSum($r);
                return $r;
            });

            return ResourceResource::collection($items)->additional([
                'meta' => ['total' => $items->count()],
            ]);
        }

        $perPage = (int) $perPage;
        $perPage = max(1, min(200, $perPage));

        $page = $q->paginate($perPage);

        $page->getCollection()->transform(function ($r) {
            $r->fifty_six_formats_count = $this->mapFormatSum($r);
            return $r;
        });

        return ResourceResource::collection($page)->additional([
            'meta' => ['total' => $page->total()],
        ]);
    }

    public function show($id)
    {
        $q = Resource::query()
            ->with(['officeEmployee:id,name,code_number,phone_number,father_name,nick_name'])
            ->whereKey($id);

        $this->withCounts($q);

        $row = $q->firstOrFail();
        $row->fifty_six_formats_count = $this->mapFormatSum($row);

        return new ResourceResource($row);
    }
}
