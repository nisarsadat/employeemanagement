<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\FormatZone;
use App\Http\Resources\FormatZoneResource;
use App\Http\Requests\StoreFormatZoneRequest;
use App\Http\Requests\UpdateFormatZoneRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Services\NotificationService;

class FormatZoneController extends Controller
{
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int)$page > 0;
        $isValidPerPage = is_numeric($perPage) && (int)$perPage > 0;

        $search = trim((string)$request->query('search', ''));

        $q = FormatZone::query()
            ->when($search !== '', fn ($x) =>
                $x->where('name', 'like', "%{$search}%")
            )
            ->orderByDesc('id');

        if ($isValidPage && $isValidPerPage) {
            return FormatZoneResource::collection(
                $q->paginate((int)$perPage, ['*'], 'page', (int)$page)
            );
        }

        return FormatZoneResource::collection($q->get());
    }

    public function store(StoreFormatZoneRequest $request)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $user) {
                $item = FormatZone::create($request->validated());

                NotificationService::record(
                    $user,
                    module: 'فارمت زون',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'نوم: ' . $item->name,
                    routeName: 'format-zones',
                    routeParams: ['id' => $item->id],
                    level: 'success',
                    changes: null
                );

                return FormatZoneResource::make($item);
            });
        } catch (QueryException $e) {
            if ((int)($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'message' => 'دا نوم له مخکې نه موجود دی.',
                    'errors'  => ['name' => ['دا نوم له مخکې نه موجود دی.']],
                ], 422);
            }
            throw $e;
        }
    }

    public function show(FormatZone $formatZone)
    {
        return FormatZoneResource::make($formatZone);
    }

    public function update(UpdateFormatZoneRequest $request, FormatZone $formatZone)
    {
        $user = $request->user();

        return DB::transaction(function () use ($request, $formatZone, $user) {
            $old = $formatZone->name;

            $formatZone->update($request->validated());

            NotificationService::record(
                $user,
                module: 'فارمت زون',
                action: 'updated',
                title: 'ریکارډ اصلاح شو',
                message: 'نوم: ' . $formatZone->name,
                routeName: 'format-zones',
                routeParams: ['id' => $formatZone->id],
                level: 'info',
                changes: [
                    'name' => ['old' => $old, 'new' => $formatZone->name],
                ]
            );

            return FormatZoneResource::make($formatZone);
        });
    }

    public function destroy(Request $request, FormatZone $formatZone)
    {
        $user = $request->user();
        $name = $formatZone->name;

        $formatZone->delete();

        NotificationService::record(
            $user,
            module: 'فارمت زون',
            action: 'deleted',
            title: 'ریکارډ حذف شو',
            message: 'نوم: ' . $name,
            routeName: null,
            routeParams: ['format-zones'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
}
