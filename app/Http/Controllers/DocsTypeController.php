<?php

namespace App\Http\Controllers;

use App\Http\Resources\DocsTypeResource;
use App\Models\DocsType;
use App\Services\NotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocsTypeController extends Controller
{
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));

        $q = DocsType::query()
            ->when($search !== '', fn ($x) => $x->where('name', 'like', "%{$search}%"))
            ->orderByDesc('id');

        if ($isValidPage && $isValidPerPage) {
            return DocsTypeResource::collection(
                $q->paginate((int) $perPage, ['*'], 'page', (int) $page)
            );
        }

        return DocsTypeResource::collection($q->get());
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'unique:docs_types,name'],
        ], [
            'name.required' => 'د سند ډول نوم ضروري دی.',
            'name.unique'   => 'دا د سند ډول نوم له مخکې نه موجود دی.',
        ]);

        try {
            return DB::transaction(function () use ($data, $user) {
                $item = DocsType::create($data);

                NotificationService::record(
                    $user,
                    module: 'نوع سند',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'نوم: ' . $item->name,
                    routeName: 'docs-types',
                    routeParams: ['id' => $item->id],
                    level: 'success',
                    changes: null
                );

                return DocsTypeResource::make($item);
            });
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'message' => 'دا د سند ډول نوم له مخکې نه موجود دی.',
                    'errors'  => [
                        'name' => ['دا د سند ډول نوم له مخکې نه موجود دی.'],
                    ],
                ], 422);
            }

            throw $e;
        }
    }

    public function show(DocsType $docsType)
    {
        return DocsTypeResource::make($docsType);
    }

    public function update(Request $request, DocsType $docsType)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'unique:docs_types,name,' . $docsType->id],
        ], [
            'name.required' => 'د سند ډول نوم ضروري دی.',
            'name.unique'   => 'دا د سند ډول نوم له مخکې نه موجود دی.',
        ]);

        try {
            return DB::transaction(function () use ($data, $docsType, $user) {
                $old = $docsType->name;

                $docsType->update($data);

                NotificationService::record(
                    $user,
                    module: 'نوع سند',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'نوم: ' . $docsType->name,
                    routeName: 'docs-types',
                    routeParams: ['id' => $docsType->id],
                    level: 'info',
                    changes: [
                        'name' => ['old' => $old, 'new' => $docsType->name],
                    ]
                );

                return DocsTypeResource::make($docsType);
            });
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'message' => 'دا د سند ډول نوم له مخکې نه موجود دی.',
                    'errors'  => [
                        'name' => ['دا د سند ډول نوم له مخکې نه موجود دی.'],
                    ],
                ], 422);
            }

            throw $e;
        }
    }

    public function destroy(Request $request, DocsType $docsType)
    {
        $user = $request->user();
        $name = $docsType->name;

        $docsType->delete();

        NotificationService::record(
            $user,
            module: 'نوع سند',
            action: 'deleted',
            title: 'ریکارډ حذف شو',
            message: 'نوم: ' . $name,
            routeName: null,
            routeParams: ['docs-types'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
}