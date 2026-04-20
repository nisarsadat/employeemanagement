<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MarjaResource;
use App\Models\Marja;
use App\Services\NotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarjaController extends Controller
{
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));

        $q = Marja::query()
            ->when($search !== '', fn ($x) => $x->where('name', 'like', "%{$search}%"))
            ->orderByDesc('id');

        if ($isValidPage && $isValidPerPage) {
            return MarjaResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return MarjaResource::collection($q->get());
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'unique:marjas,name'],
        ], [
            'name.required' => 'د مرجع نوم ضروري دی.',
            'name.unique'   => 'دا د مرجع نوم له مخکې نه موجود دی.',
        ]);

        try {
            return DB::transaction(function () use ($data, $user) {
                $item = Marja::create($data);

                NotificationService::record(
                    $user,
                    module: 'مرجع',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'نوم: ' . $item->name,
                    routeName: 'marjas',
                    routeParams: ['id' => $item->id],
                    level: 'success',
                    changes: null
                );

                return MarjaResource::make($item);
            });
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'message' => 'دا د مرجع نوم له مخکې نه موجود دی.',
                    'errors'  => [
                        'name' => ['دا د مرجع نوم له مخکې نه موجود دی.'],
                    ],
                ], 422);
            }

            throw $e;
        }
    }

    public function show(Marja $marja)
    {
        return MarjaResource::make($marja);
    }

    public function update(Request $request, Marja $marja)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'unique:marjas,name,' . $marja->id],
        ], [
            'name.required' => 'د مرجع نوم ضروري دی.',
            'name.unique'   => 'دا د مرجع نوم له مخکې نه موجود دی.',
        ]);

        try {
            return DB::transaction(function () use ($data, $marja, $user) {
                $old = $marja->name;

                $marja->update($data);

                NotificationService::record(
                    $user,
                    module: 'مرجع',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'نوم: ' . $marja->name,
                    routeName: 'marjas',
                    routeParams: ['id' => $marja->id],
                    level: 'info',
                    changes: [
                        'name' => ['old' => $old, 'new' => $marja->name],
                    ]
                );

                return MarjaResource::make($marja);
            });
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'message' => 'دا د مرجع نوم له مخکې نه موجود دی.',
                    'errors'  => [
                        'name' => ['دا د مرجع نوم له مخکې نه موجود دی.'],
                    ],
                ], 422);
            }

            throw $e;
        }
    }

    public function destroy(Request $request, Marja $marja)
    {
        $user = $request->user();
        $name = $marja->name;

        $marja->delete();

        NotificationService::record(
            $user,
            module: 'مرجع',
            action: 'deleted',
            title: 'ریکارډ حذف شو',
            message: 'نوم: ' . $name,
            routeName: null,
            routeParams: ['marjas'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
}