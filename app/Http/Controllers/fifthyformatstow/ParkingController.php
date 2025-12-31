<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreParkingRequest;
use App\Http\Requests\fifthyformatstow\UpdateParkingRequest;
use App\Http\Resources\fifthyformatstow\ParkingResource;
use App\Models\fifthyformatstow\Parking;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParkingController extends Controller
{
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا پارکینګ مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'دا پارکینګ مخکې ثبت شوی دی (نوم/ډول، مسؤل معلومات او پته یو شان دي).',
                ],
            ],
        ];
    }

    private function isDuplicateKey(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');
        $rid     = $request->input('resource_id');

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = in_array($request->input('sort_dir'), ['asc','desc'], true) ? $request->input('sort_dir') : 'desc';

        $q = Parking::query()->with([
            'resource' => fn ($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('parking_name', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('responsible_phone', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%");
            });
        }

        if ($rid) {
            $q->where('resource_id', $rid);
        }

        $q->orderBy($sortBy, $sortDir);

        return ParkingResource::collection($q->paginate($perPage));
    }

    public function store(StoreParkingRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $user) {
                $parking = Parking::create($request->validated());

                $parking->load([
                    'resource' => fn ($qr) => $qr->select('id','name','code_number','phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'پارکینګ',
                    action: 'created',
                    title: 'پارکینګ جدید ثبت شد',
                    message: 'نام پارکینګ: ' . ($parking->parking_name ?? ''),
                    routeName: 'parkings',               // ⚠️ adjust to your real route name
                    routeParams: ['id' => $parking->id],
                    level: 'success',
                    changes: null
                );

                return (new ParkingResource($parking))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    public function show(Parking $parking)
    {
        $parking->load([
            'resource' => fn ($qr) => $qr->select('id','name','code_number','phone_number'),
        ]);

        return ParkingResource::make($parking);
    }

    public function update(UpdateParkingRequest $request, Parking $parking)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $parking, $user) {
                $validated = $request->validated();

                // 🧮 changes BEFORE update
                $original = $parking->getOriginal();
                $changes  = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                $parking->update($validated);

                $parking->load([
                    'resource' => fn ($qr) => $qr->select('id','name','code_number','phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'پارکینګ',
                    action: 'updated',
                    title: 'پارکینګ اصلاح شد',
                    message: 'نام پارکینګ: ' . ($parking->parking_name ?? ''),
                    routeName: 'parkings',
                    routeParams: ['id' => $parking->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return ParkingResource::make($parking);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Parking $parking)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($parking, $user) {
            $name = $parking->parking_name;

            $parking->delete();

            NotificationService::record(
                $user,
                module: 'پارکینګ',
                action: 'deleted',
                title: 'پارکینګ حذف شد',
                message: 'نام پارکینګ: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['parkings'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = $request->input('ids', []);
        $ids = is_array($ids) ? array_values(array_filter($ids)) : [];

        return DB::transaction(function () use ($ids, $user) {
            if (empty($ids)) {
                return response()->noContent();
            }

            Parking::whereIn('id', $ids)->delete();

            NotificationService::record(
                $user,
                module: 'پارکینګ',
                action: 'deleted',
                title: 'پارکینګ‌ها به‌صورت گروهی حذف شد',
                message: 'تعداد: ' . count($ids),
                routeName: null,
                routeParams: ['parkings'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}
