<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreCriminalCapturePlaceRequest;
use App\Http\Requests\fifthyformatfour\UpdateCriminalCapturePlaceRequest;
use App\Http\Resources\fifthyformatfour\CriminalCapturePlaceResource;
use App\Models\fifthyformatfour\CriminalCapturePlace;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CriminalCapturePlaceController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key error code
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'این ثبت قبلاً موجود است.',
            'errors' => [
                'duplicate' => [
                    'محل دستگیری با همین مشخصات (مشخصات فرد، نوع جرم و آدرس) قبلاً در سیستم ثبت شده است.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $query = CriminalCapturePlace::query()
            ->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('father_name', 'like', "%{$s}%")
                  ->orWhere('office', 'like', "%{$s}%")
                  ->orWhere('village', 'like', "%{$s}%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        // ✅ Same pattern: invalid pagination → return ALL
        if ($perPageInt === false || $pageInt === false) {
            return CriminalCapturePlaceResource::collection(
                $query->orderByDesc('id')->get()
            );
        }

        $rows = $query->orderByDesc('id')
            ->paginate($perPageInt, ['*'], 'page', $pageInt);

        return CriminalCapturePlaceResource::collection($rows);
    }

    public function store(StoreCriminalCapturePlaceRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $user) {
                $row = CriminalCapturePlace::create($request->validated());
                $row->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'محل دستگیری',
                    action: 'created',
                    title: 'محل دستگیری جدید ثبت شد',
                    message: 'نام فرد: ' . ($row->name ?? ''),
                    routeName: 'criminal-capture-places', // adjust if needed
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return new CriminalCapturePlaceResource($row);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(CriminalCapturePlace $criminalCapturePlace)
    {
        $criminalCapturePlace->load(['resource:id,name,code_number,phone_number']);
        return new CriminalCapturePlaceResource($criminalCapturePlace);
    }

    public function update(UpdateCriminalCapturePlaceRequest $request, CriminalCapturePlace $criminalCapturePlace)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $criminalCapturePlace, $user) {
                // 🧮 changes BEFORE update
                $original  = $criminalCapturePlace->getOriginal();
                $validated = $request->validated();
                $changes   = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original)) {
                        if ($original[$field] != $newValue) {
                            $changes[$field] = [
                                'old' => $original[$field],
                                'new' => $newValue,
                            ];
                        }
                    }
                }

                $criminalCapturePlace->update($validated);
                $criminalCapturePlace->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED
                NotificationService::record(
                    $user,
                    module: 'محل دستگیری',
                    action: 'updated',
                    title: 'محل دستگیری اصلاح شد',
                    message: 'نام فرد: ' . ($criminalCapturePlace->name ?? ''),
                    routeName: 'criminal-capture-places',
                    routeParams: ['id' => $criminalCapturePlace->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return new CriminalCapturePlaceResource($criminalCapturePlace);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, CriminalCapturePlace $criminalCapturePlace)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($criminalCapturePlace, $user) {
            $name = $criminalCapturePlace->name;

            $criminalCapturePlace->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'محل دستگیری',
                action: 'deleted',
                title: 'محل دستگیری حذف شد',
                message: 'نام فرد: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['criminal-capture-places'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}
