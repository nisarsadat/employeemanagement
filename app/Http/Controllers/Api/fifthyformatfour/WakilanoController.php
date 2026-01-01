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

class WakilanoController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key
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

    /**
     * GET /api/wakilanos
     * - Eager-load resource:id,name,code_number,phone_number
     * - Search (optional): ?search=...
     * - Pagination: if perPage/page missing or invalid => return ALL (no pagination)
     */
    public function index(Request $request)
    {
        $query = Wakilano::query()
            ->with(['resource:id,name,code_number,phone_number']);

        // Simple search
        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('f_name', 'like', "%$s%")
                  ->orWhere('grand_father_name', 'like', "%$s%")
                  ->orWhere('phone_number', 'like', "%$s%")
                  ->orWhere('now_village', 'like', "%$s%");
            });
        }

        // Validate pagination inputs
        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        // Fallback → return ALL if inputs are missing or invalid
        if ($perPageInt === false || $pageInt === false) {
            $rows = $query->orderByDesc('id')->get();
            return WakilanoResource::collection($rows);
        }

        // Paginated path
        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);

        return WakilanoResource::collection($rows);
    }

    public function store(StoreWakilanoRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $user) {
                $row = Wakilano::create($request->validated());
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'وکیلان',
                    action: 'created',
                    title: 'نوی وکیل ثبت شو',
                    message: 'نوم: ' . ($row->name ?? ''),
                    routeName: 'wakilanos',              // ⚠️ set to your real route name
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
                // 🧮 build changes BEFORE update
                $original  = $wakilano->getOriginal();
                $validated = $request->validated();
                $changes   = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original)) {
                        $oldValue = $original[$field];
                        if ($oldValue != $newValue) {
                            $changes[$field] = [
                                'old' => $oldValue,
                                'new' => $newValue,
                            ];
                        }
                    }
                }

                $wakilano->update($validated);
                $wakilano->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'وکیلان',
                    action: 'updated',
                    title: 'وکیل اصلاح شو',
                    message: 'نوم: ' . ($wakilano->name ?? ''),
                    routeName: 'wakilanos',              // ⚠️ set to your real route name
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

            $wakilano->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'وکیلان',
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
