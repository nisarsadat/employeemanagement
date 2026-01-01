<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreComplexRequest;
use App\Http\Requests\fifthyformatfour\UpdateComplexRequest;
use App\Http\Resources\fifthyformatfour\ComplexResource;
use App\Models\fifthyformatfour\Complex;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComplexController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'دا کمپلکس مخکې نه ثبت شوی دی.',
            'errors'  => [
                'duplicate' => [
                    'د همدې ځای او معلوماتو (ولايت/زون/ولسوالۍ/کلی/ځانګړی ځای/کوڅه/کور + نوم/فعالیت ډول/د فعالیت ځای) سره کمپلکس مخکې موجود دی.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $query = Complex::query()->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('complex_name', 'like', "%$s%")
                  ->orWhere('province', 'like', "%$s%")
                  ->orWhere('zone', 'like', "%$s%")
                  ->orWhere('district', 'like', "%$s%")
                  ->orWhere('village', 'like', "%$s%")
                  ->orWhere('specefic_place', 'like', "%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pageInt    = filter_var($page, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($perPageInt === false || $pageInt === false) {
            return ComplexResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return ComplexResource::collection($rows);
    }

    public function store(StoreComplexRequest $request)
    {
        $user = $request->user(); // 🔔 who did this

        try {
            return DB::transaction(function () use ($request, $user) {
                $row = Complex::create($request->validated());
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'کمپلکس',
                    action: 'created',
                    title: 'نوی کمپلکس ثبت شو',
                    message: 'د کمپلکس نوم: ' . ($row->complex_name ?? ''),
                    routeName: 'complexes',               // ⚠️ set to your real route name
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return new ComplexResource($row);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(Complex $complex)
    {
        $complex->load(['resource:id,name,code_number,phone_number']);
        return new ComplexResource($complex);
    }

    public function update(UpdateComplexRequest $request, Complex $complex)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $complex, $user) {
                $original  = $complex->getOriginal();
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

                $complex->update($validated);
                $complex->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'کمپلکس',
                    action: 'updated',
                    title: 'کمپلکس اصلاح شو',
                    message: 'د کمپلکس نوم: ' . ($complex->complex_name ?? ''),
                    routeName: 'complexes',               // ⚠️ set to your real route name
                    routeParams: ['id' => $complex->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new ComplexResource($complex);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Complex $complex)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($complex, $user) {
            $name = $complex->complex_name;

            $complex->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'کمپلکس',
                action: 'deleted',
                title: 'کمپلکس حذف شو',
                message: 'د کمپلکس نوم: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['complexes'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}
