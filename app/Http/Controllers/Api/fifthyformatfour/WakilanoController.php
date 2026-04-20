<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreWakilanoRequest;
use App\Http\Requests\fifthyformatfour\UpdateWakilanoRequest;
use App\Http\Resources\fifthyformatfour\WakilanoResource;
use App\Models\fifthyformatfour\Wakilano;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class WakilanoController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'دا وکیل/استازی مخکې نه ثبت شوی دی.',
            'errors'  => [
                'duplicate' => [
                    'د همدې مشخصاتو (نوم/د پلار نوم/د نیکه نوم + اصلي ولایت/ولسوالۍ/کلی) سره ریکارډ مخکې موجود دی.',
                ],
            ],
        ], 422);
    }

    private function normalizePublicPath(?string $path): ?string
    {
        if (!$path) return null;
        $p = $path;
        if (str_starts_with($p, 'storage/')) {
            $p = substr($p, strlen('storage/'));
        }
        return $p;
    }

    private function storePicture(Request $request, string $field = 'picture'): ?string
    {
        if (!$request->hasFile($field)) return null;

        $file = $request->file($field);
        if (!$file) return null;

        // store in public disk
        return $file->store('wakilanos/pictures', 'public');
    }

    /**
     * GET /api/wakilanos
     */
    public function index(Request $request)
    {
        $query = Wakilano::query()
            ->with(['resource:id,name,code_number,phone_number']);

        $search = trim((string) $request->query('search', ''));
        $searchField = trim((string) $request->query('search_field', ''));
        $searchValue = trim((string) $request->query('search_value', ''));

        $allowedSearchFields = [

                'name',
        'f_name',
        'grand_father_name',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',
        'hawza',
        'nahya',
        'Guzar',
        'coordinates',
        'place_tafseel',
        'old_job',
        'now_job',
        'home_quantity',
        'tanzim',
        'agent_quantity',
        'phone_number',
        'history_form',
        'Province_addr',
        'resource_id',
        'note',
        ];

        if ($search !== '' && $searchField === '') {
            $query->where(function (Builder $q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('f_name', 'like', "%{$search}%")
                  ->orWhere('grand_father_name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('now_village', 'like', "%{$search}%");
            });
        }

        if ($searchField !== '' && in_array($searchField, $allowedSearchFields, true)) {
            $value = $searchValue !== '' ? $searchValue : $search;

            if ($value !== '') {
                $query->where($searchField, 'like', "%{$value}%");
            }
        }

        $perPage = $request->query('perPage', $request->query('per_page'));
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pageInt    = filter_var($page, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($perPageInt === false || $pageInt === false) {
            $rows = $query->orderByDesc('id')->get();
            return WakilanoResource::collection($rows);
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);

        return WakilanoResource::collection($rows);
    }

    /**
     * GET /wakilanos/filter-options
     */
    public function filterOptions(Request $request)
    {
        $field = trim((string) $request->query('field', ''));
        $q = trim((string) $request->query('q', ''));

        $allowedFields = [

                'name',
        'f_name',
        'grand_father_name',
        'main_province',
        'main_district',
        'main_village',
        'now_province',
        'now_zone',
        'now_district',
        'now_village',
        'now_specific_place',
        'now_street_number',
        'now_home_number',
        'hawza',
        'nahya',
        'Guzar',
        'coordinates',
        'place_tafseel',
        'old_job',
        'now_job',
        'home_quantity',
        'tanzim',
        'agent_quantity',
        'phone_number',
        'history_form',
        'Province_addr',
        'resource_id',
        'note',
        ];

        if ($field === '' || !in_array($field, $allowedFields, true)) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $query = Wakilano::query()
            ->select($field)
            ->whereNotNull($field)
            ->where($field, '!=', '');

        if ($q !== '') {
            $query->where($field, 'like', "%{$q}%");
        }

        $options = $query
            ->distinct()
            ->pluck($field)
            ->filter(fn ($v) => $v !== null && trim((string) $v) !== '')
            ->values()
            ->map(fn ($v) => [
                'title' => (string) $v,
                'value' => (string) $v,
            ]);

        return response()->json([
            'data' => [
                'options' => $options,
            ],
        ]);
    }

    public function store(StoreWakilanoRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $user) {
                $data = $request->validated();

                // ✅ picture upload
                $pic = $this->storePicture($request, 'picture');
                if ($pic) {
                    $data['picture'] = $pic;
                }

                $row = Wakilano::create($data);
                $row->load(['resource:id,name,code_number,phone_number']);

                NotificationService::record(
                    $user,
                    module: 'وکیلان او ملکان',
                    action: 'created',
                    title: 'نوی وکیل ثبت شو',
                    message: 'نوم: ' . ($row->name ?? ''),
                    routeName: 'wakilanos',
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return new WakilanoResource($row);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(Wakilano $wakilano)
    {
        $wakilano->load(['resource:id,name,code_number,phone_number']);
        return new WakilanoResource($wakilano);
    }

    public function update(UpdateWakilanoRequest $request, Wakilano $wakilano)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $wakilano, $user) {
                $original  = $wakilano->getOriginal();
                $validated = $request->validated();
                $changes   = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original)) {
                        $oldValue = $original[$field];
                        if ($oldValue != $newValue) {
                            $changes[$field] = ['old' => $oldValue, 'new' => $newValue];
                        }
                    }
                }

                // ✅ remove picture request (from Vue: _remove_picture = 1)
                $removePic = (int) $request->input('_remove_picture', 0) === 1;

                if ($removePic && $wakilano->picture) {
                    $old = $this->normalizePublicPath($wakilano->picture);
                    if ($old) Storage::disk('public')->delete($old);
                    $validated['picture'] = null;
                    $changes['picture'] = ['old' => $wakilano->picture, 'new' => null];
                }

                // ✅ new picture upload
                $newPic = $this->storePicture($request, 'picture');
                if ($newPic) {
                    // delete old
                    if ($wakilano->picture) {
                        $old = $this->normalizePublicPath($wakilano->picture);
                        if ($old) Storage::disk('public')->delete($old);
                    }
                    $validated['picture'] = $newPic;
                    $changes['picture'] = ['old' => $wakilano->picture, 'new' => $newPic];
                }

                $wakilano->update($validated);
                $wakilano->load(['resource:id,name,code_number,phone_number']);

                NotificationService::record(
                    $user,
                    module: 'وکیلان او ملکان',
                    action: 'updated',
                    title: 'وکیل اصلاح شو',
                    message: 'نوم: ' . ($wakilano->name ?? ''),
                    routeName: 'wakilanos',
                    routeParams: ['id' => $wakilano->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new WakilanoResource($wakilano);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Wakilano $wakilano)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($wakilano, $user) {
            $name = $wakilano->name;

            // ✅ delete picture file
            if ($wakilano->picture) {
                $old = $this->normalizePublicPath($wakilano->picture);
                if ($old) Storage::disk('public')->delete($old);
            }

            $wakilano->delete();

            NotificationService::record(
                $user,
                module: 'وکیلان او ملکان',
                action: 'deleted',
                title: 'وکیل حذف شو',
                message: 'نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['wakilanos'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}