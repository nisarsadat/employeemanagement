<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\DocsType;
use App\Http\Resources\DocsTypeResource;
use App\Http\Requests\StoreDocsTypeRequest;
use App\Http\Requests\UpdateDocsTypeRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Services\NotificationService;

class DocsTypeController extends Controller
{
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int)$page > 0;
        $isValidPerPage = is_numeric($perPage) && (int)$perPage > 0;

        $search = trim((string)$request->query('search', ''));

        $q = DocsType::query()
            ->when($search !== '', fn ($x) =>
                $x->where('name', 'like', "%{$search}%")
            )
            ->orderByDesc('id');

        if ($isValidPage && $isValidPerPage) {
            return DocsTypeResource::collection(
                $q->paginate((int)$perPage, ['*'], 'page', (int)$page)
            );
        }

        return DocsTypeResource::collection($q->get());
    }

    public function store(StoreDocsTypeRequest $request)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $user) {
                $item = DocsType::create($request->validated());

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
            if ((int)($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'message' => 'دا نوم له مخکې نه موجود دی.',
                    'errors'  => ['name' => ['دا نوم له مخکې نه موجود دی.']],
                ], 422);
            }
            throw $e;
        }
    }

    public function show(DocsType $docsType)
    {
        return DocsTypeResource::make($docsType);
    }

    public function update(UpdateDocsTypeRequest $request, DocsType $docsType)
    {
        $user = $request->user();

        return DB::transaction(function () use ($request, $docsType, $user) {
            $old = $docsType->name;

            $docsType->update($request->validated());

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
