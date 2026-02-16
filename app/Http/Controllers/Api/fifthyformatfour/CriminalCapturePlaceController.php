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
use Illuminate\Support\Facades\Storage;

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
        $pageInt    = filter_var($page, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

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

    /**
     * POST /criminal-capture-places
     * Accepts:
     * - picture as FILE or STRING
     */
    public function store(StoreCriminalCapturePlaceRequest $request)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $data, $user) {

                // ✅ picture: FILE OR STRING (same style as SaeenFasadController)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/criminal-capture-places', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $row = CriminalCapturePlace::create($data);
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

                return (new CriminalCapturePlaceResource($row))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function show(CriminalCapturePlace $criminalCapturePlace)
    {
        $criminalCapturePlace->load(['resource:id,name,code_number,phone_number']);
        return new CriminalCapturePlaceResource($criminalCapturePlace);
    }

    /**
     * PUT /criminal-capture-places/{criminalCapturePlace}
     * Accepts:
     * - picture as FILE or STRING (replaces old file if new uploaded)
     */
    public function update(UpdateCriminalCapturePlaceRequest $request, CriminalCapturePlace $criminalCapturePlace)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $criminalCapturePlace, $data, $user) {
                // 🧮 changes BEFORE update
                $original = $criminalCapturePlace->getOriginal();
                $changes  = [];

                foreach ($data as $field => $newValue) {
                    if (array_key_exists($field, $original)) {
                        if ($original[$field] != $newValue) {
                            $changes[$field] = [
                                'old' => $original[$field],
                                'new' => $newValue,
                            ];
                        }
                    }
                }

                // ✅ picture: replace file if uploaded
                if ($request->hasFile('picture')) {
                    if ($criminalCapturePlace->picture && str_starts_with($criminalCapturePlace->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $criminalCapturePlace->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/criminal-capture-places', 'public');
                    $data['picture'] = "storage/{$path}";

                    $changes['picture'] = [
                        'old' => $original['picture'] ?? null,
                        'new' => $data['picture'],
                    ];
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $criminalCapturePlace->update($data);
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
            if ($this->isDuplicateKey($e)) return $this->duplicateResponse();
            throw $e;
        }
    }

    public function destroy(Request $request, CriminalCapturePlace $criminalCapturePlace)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($criminalCapturePlace, $user) {
            $name = $criminalCapturePlace->name;

            // ✅ delete picture file (same style as SaeenFasadController)
            if ($criminalCapturePlace->picture && str_starts_with($criminalCapturePlace->picture, 'storage/')) {
                $old = str_replace('storage/', '', $criminalCapturePlace->picture);
                Storage::disk('public')->delete($old);
            }

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
