<?php

namespace App\Http\Controllers;

use App\Models\Information;
use App\Models\InformationDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreInformationRequest;
use App\Http\Requests\UpdateInformationRequest;
use App\Http\Resources\InformationResource;
use App\Services\NotificationService; // 🔔 notifications

class InformationController extends Controller
{
    public function index(Request $request)
    {
        $page    = $request->query('page');
        $perPage = $request->query('perPage', $request->query('per_page'));

        $isValidPage    = is_numeric($page) && (int) $page > 0;
        $isValidPerPage = is_numeric($perPage) && (int) $perPage > 0;

        $search = trim((string) $request->query('search', ''));

        // ✅ DATE OPTION + FILTER (created_at)
        // - date=YYYY-MM-DD (single day)
        // - date_from=YYYY-MM-DD & date_to=YYYY-MM-DD (range)
        $date     = trim((string) $request->query('date', ''));
        $dateFrom = trim((string) $request->query('date_from', $request->query('from_date', '')));
        $dateTo   = trim((string) $request->query('date_to', $request->query('to_date', '')));

        $q = Information::query()
            ->with([
                'resource',
                'officeEmployee',
                'officeEmployeeInfo',
                'documents',
                'finances',
            ])
            ->when($search !== '', function ($sub) use ($search) {
                $sub->where(function ($x) use ($search) {
                    $x->where('name', 'like', "%{$search}%")
                      ->orWhere('father_name', 'like', "%{$search}%")
                      ->orWhere('nick_name', 'like', "%{$search}%");
                });
            })
            // ✅ DATE filter logic
            ->when($date !== '', function ($sub) use ($date) {
                $sub->whereDate('created_at', $date);
            })
            ->when($dateFrom !== '' && $dateTo !== '', function ($sub) use ($dateFrom, $dateTo) {
                $sub->whereDate('created_at', '>=', $dateFrom)
                    ->whereDate('created_at', '<=', $dateTo);
            })
            ->when($dateFrom !== '' && $dateTo === '' && $date === '', function ($sub) use ($dateFrom) {
                $sub->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($dateTo !== '' && $dateFrom === '' && $date === '', function ($sub) use ($dateTo) {
                $sub->whereDate('created_at', '<=', $dateTo);
            })
            ->orderByDesc('id');

        if ($isValidPage && $isValidPerPage) {
            $data = $q->paginate((int) $perPage, ['*'], 'page', (int) $page);
            return InformationResource::collection($data);
        }

        return InformationResource::collection($q->get());
    }

    public function store(StoreInformationRequest $request)
    {
        $user = $request->user(); // 🔔 who did this

        return DB::transaction(function () use ($request, $user) {
            $info = Information::create($request->validated());

            // 📎 documents
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store("information_docs/{$info->id}", 'public');
                    InformationDocument::create([
                        'information_id' => $info->id,
                        'file_name'      => $file->getClientOriginalName(),
                        'file_path'      => $path,
                        'mime_type'      => $file->getMimeType(),
                        'size_bytes'     => $file->getSize(),
                    ]);
                }
            }

            $info->load([
                'resource',
                'officeEmployee',
                'officeEmployeeInfo',
                'documents',
                'finances',
            ]);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'اطلاع',
                action: 'created',
                title: 'اطلاع جدید ثبت شد',
                message: 'نام اطلاع: ' . ($info->name ?? ''),
                routeName: 'informations',         // ⚠️ use your real route name if different
                routeParams: ['id' => $info->id],
                level: 'success',
                changes: null                      // no changes on create
            );

            return new InformationResource($info);
        });
    }

    public function show($id)
    {
        // 🔹 Fetch by id + relations
        $information = Information::with([
            'resource',
            'officeEmployee',
            'officeEmployeeInfo',
            'documents',
            'finances',
        ])->findOrFail($id);

        return new InformationResource($information);
    }

    public function update(UpdateInformationRequest $request, $id)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($request, $id, $user) {
            $information = Information::with([
                'resource',
                'officeEmployee',
                'officeEmployeeInfo',
                'documents',
                'finances',
            ])->findOrFail($id);

            // 🧮 build changes BEFORE update
            $original  = $information->getOriginal();
            $validated = $request->validated();
            $changes   = [];

            foreach ($validated as $field => $newValue) {
                if (array_key_exists($field, $original)) {
                    $oldValue = $original[$field];

                    // loose comparison is OK for now; adjust if you need strict
                    if ($oldValue != $newValue) {
                        $changes[$field] = [
                            'old' => $oldValue,
                            'new' => $newValue,
                        ];
                    }
                }
            }

            // 🔄 apply update
            $information->update($validated);

            // 🔔 Reset missing-result notification tracking when result is provided
            if (
                array_key_exists('result', $validated) &&
                !is_null($validated['result']) &&
                trim((string) $validated['result']) !== ''
            ) {
                $information->forceFill([
                    'result_missing_first_notified_at' => null,
                    'result_missing_last_notified_at'  => null,
                    'result_missing_notify_count'      => 0,
                ]);
                $information->save(); // still a 2nd query, but cleaner on model state
            }

            // 📎 new documents
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store("information_docs/{$information->id}", 'public');
                    InformationDocument::create([
                        'information_id' => $information->id,
                        'file_name'      => $file->getClientOriginalName(),
                        'file_path'      => $path,
                        'mime_type'      => $file->getMimeType(),
                        'size_bytes'     => $file->getSize(),
                    ]);
                }
            }

            $information->load([
                'resource',
                'officeEmployee',
                'officeEmployeeInfo',
                'documents',
                'finances',
            ]);

            // 🔔 Notification: UPDATED + changes
            NotificationService::record(
                $user,
                module: 'اطلاع',
                action: 'updated',
                title: 'اطلاع اصلاح شد',
                message: 'نام اطلاع: ' . ($information->name ?? ''),
                routeName: 'informations',         // ⚠️ adjust if needed
                routeParams: ['id' => $information->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return new InformationResource($information);
        });
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($id, $user) {
            $information = Information::with('documents')->findOrFail($id);

            $name = $information->name;

            // 🗑 delete files
            foreach ($information->documents as $doc) {
                Storage::disk('public')->delete($doc->file_path);
            }

            $information->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'اطلاع',
                action: 'deleted',
                title: 'اطلاع حذف شد',
                message: 'نام اطلاع: ' . ($name ?? ''),
                routeName: null,                   // no direct link after delete
                routeParams: ['informations'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}
