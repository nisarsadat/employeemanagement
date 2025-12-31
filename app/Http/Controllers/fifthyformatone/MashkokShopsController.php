<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreMashkokShopsRequest;
use App\Http\Requests\fifthyformatone\UpdateMashkokShopsRequest;
use App\Http\Resources\fifthyformatone\MashkokShopsResource;
use App\Models\fifthyformatone\MashkokShops;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MashkokShopsController extends Controller
{
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا ریکارډ مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'دا دوکان/شخص مخکې ثبت شوی دی (نوم، د پلار نوم، اصلي ولایت، د تماس شمېره، د دوکان ډول او پته یو شان دي).',
                ],
            ],
        ];
    }

    private function isDuplicateKeyException(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    /** GET /mashkok-shops */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');
        $rid     = $request->input('resource_id');

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = in_array($request->input('sort_dir'), ['asc', 'desc'], true) ? $request->input('sort_dir') : 'desc';

        $q = MashkokShops::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        if ($search) {
            $q->where(function ($w) use ($search) {
                $w->where('province', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%")
                  ->orWhere('market_name', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($rid) {
            $q->where('resource_id', $rid);
        }

        $q->orderBy($sortBy, $sortDir);

        return MashkokShopsResource::collection($q->paginate($perPage));
    }

    /** POST /mashkok-shops */
    public function store(StoreMashkokShopsRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $user) {
                $record = MashkokShops::create($request->validated());

                $record->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'دوکان‌های مشکوک',
                    action: 'created',
                    title: 'دوکان مشکوک جدید ثبت شد',
                    message: 'نام: ' . ($record->name ?? '') . ' | بازار: ' . ($record->market_name ?? ''),
                    routeName: 'mashkok-shops',          // ⚠️ set your real route name
                    routeParams: ['id' => $record->id],
                    level: 'success',
                    changes: null
                );

                return (new MashkokShopsResource($record))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    /** GET /mashkok-shops/{mashkok_shop} */
    public function show(MashkokShops $mashkok_shop)
    {
        $mashkok_shop->load([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        return MashkokShopsResource::make($mashkok_shop);
    }

    /** PUT /mashkok-shops/{mashkok_shop} */
    public function update(UpdateMashkokShopsRequest $request, MashkokShops $mashkok_shop)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $mashkok_shop, $user) {
                $validated = $request->validated();

                // 🧮 changes BEFORE update
                $original = $mashkok_shop->getOriginal();
                $changes  = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                $mashkok_shop->update($validated);

                $mashkok_shop->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'دوکان‌های مشکوک',
                    action: 'updated',
                    title: 'دوکان مشکوک اصلاح شد',
                    message: 'نام: ' . ($mashkok_shop->name ?? '') . ' | بازار: ' . ($mashkok_shop->market_name ?? ''),
                    routeName: 'mashkok-shops',          // ⚠️ set your real route name
                    routeParams: ['id' => $mashkok_shop->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return MashkokShopsResource::make($mashkok_shop);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    /** DELETE /mashkok-shops/{mashkok_shop} */
    public function destroy(Request $request, MashkokShops $mashkok_shop)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($mashkok_shop, $user) {
            $name = $mashkok_shop->name;

            $mashkok_shop->delete();

            NotificationService::record(
                $user,
                module: 'دوکان‌های مشکوک',
                action: 'deleted',
                title: 'دوکان مشکوک حذف شد',
                message: 'نام: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['mashkok-shops'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    /** POST /mashkok-shops/bulk-delete  { "ids": [1,2,3] } */
    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = $request->input('ids', []);
        $ids = is_array($ids) ? array_values(array_filter($ids)) : [];

        return DB::transaction(function () use ($ids, $user) {
            if (empty($ids)) {
                return response()->noContent();
            }

            MashkokShops::whereIn('id', $ids)->delete();

            NotificationService::record(
                $user,
                module: 'دوکان‌های مشکوک',
                action: 'deleted',
                title: 'دوکان‌ها به‌صورت گروهی حذف شد',
                message: 'تعداد: ' . count($ids),
                routeName: null,
                routeParams: ['mashkok-shops'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}
