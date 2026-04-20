<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FormatZoneResource;
use App\Models\FormatZone;
use App\Services\NotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormatZoneController extends Controller
{
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));

        $q = FormatZone::query()
            ->when($search !== '', fn ($x) => $x->where('name', 'like', "%{$search}%"))
            ->orderByDesc('id');

        if ($isValidPage && $isValidPerPage) {
            return FormatZoneResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return FormatZoneResource::collection($q->get());
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'unique:format_zones,name'],
        ], [
            'name.required' => 'د فارمت زون نوم ضروري دی.',
            'name.unique'   => 'دا د فارمت زون نوم له مخکې نه موجود دی.',
        ]);

        try {
            return DB::transaction(function () use ($data, $user) {
                $item = FormatZone::create($data);

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
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'message' => 'دا د فارمت زون نوم له مخکې نه موجود دی.',
                    'errors'  => [
                        'name' => ['دا د فارمت زون نوم له مخکې نه موجود دی.'],
                    ],
                ], 422);
            }

            throw $e;
        }
    }

    public function show(FormatZone $formatZone)
    {
        return FormatZoneResource::make($formatZone);
    }

    public function update(Request $request, FormatZone $formatZone)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'unique:format_zones,name,' . $formatZone->id],
        ], [
            'name.required' => 'د فارمت زون نوم ضروري دی.',
            'name.unique'   => 'دا د فارمت زون نوم له مخکې نه موجود دی.',
        ]);

        try {
            return DB::transaction(function () use ($data, $formatZone, $user) {
                $old = $formatZone->name;

                $formatZone->update($data);

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
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'message' => 'دا د فارمت زون نوم له مخکې نه موجود دی.',
                    'errors'  => [
                        'name' => ['دا د فارمت زون نوم له مخکې نه موجود دی.'],
                    ],
                ], 422);
            }

            throw $e;
        }
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