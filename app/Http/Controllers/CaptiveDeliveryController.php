<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaptiveDeliveryRequest;
use App\Http\Requests\UpdateCaptiveDeliveryRequest;
use App\Http\Resources\CaptiveDeliveryResource;
use App\Models\CaptiveDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationService; // 🔔 VERY IMPORTANT

class CaptiveDeliveryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('perPage', $request->query('itemsPerPage', 10));
        $search  = trim((string) $request->query('search', ''));

        $q = CaptiveDelivery::query();

        if ($search !== '') {
            $q->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('father_name', 'like', "%{$search}%")
                    ->orWhere('nickname', 'like', "%{$search}%")
                    ->orWhere('tazkira', 'like', "%{$search}%");
            });
        }

        $q->with('documents');

        $data = $q->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'data' => CaptiveDeliveryResource::collection($data->items()),
            'meta' => [
                'current_page' => $data->currentPage(),
                'per_page'     => $data->perPage(),
                'total'        => $data->total(),
            ],
        ]);
    }

    public function store(StoreCaptiveDeliveryRequest $request)
    {
        $user = $request->user(); // 🔔 same as other controllers

        return DB::transaction(function () use ($request, $user) {
            $payload = $request->validated();

            // 📷 picture upload
            if ($request->hasFile('picture')) {
                $payload['picture'] = $request->file('picture')
                    ->store('captive_deliveries/pictures', 'public');
            }

            $capt = CaptiveDelivery::create($payload);

            // 📎 new documents[]
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents', []) as $file) {
                    $path = $file->store("captive_deliveries/{$capt->id}", 'public');

                    $capt->documents()->create([
                        'file_name'           => $file->getClientOriginalName(),
                        'file_path'           => $path,
                        'mime_type'           => $file->getMimeType(),
                        'size_bytes'          => $file->getSize(),
                        'captive_delivery_id' => $capt->id,
                    ]);
                }
            }

            $capt->load('documents');

            // 🔔 Notification: CREATED
            if ($user) {
                NotificationService::record(
                    $user,
                    module: 'captive_delivery',           // 🧩 use a consistent module key
                    action: 'created',
                    title: 'زندانی جدید تسلیم شد',
                    message: 'نام زندانی: ' . ($capt->name ?? ''),
                    routeName: 'delevirycaptiveform',     // your edit form route
                    routeParams: ['id' => $capt->id],
                    level: 'success',
                    changes: null                         // no changes on create
                );
            }

            return response()->json(['data' => new CaptiveDeliveryResource($capt)], 201);
        });
    }

    public function show($id)
    {
        $captiveDelivery = CaptiveDelivery::with('documents')->findOrFail($id);

        return response()->json([
            'data' => new CaptiveDeliveryResource($captiveDelivery),
        ]);
    }

    public function update(UpdateCaptiveDeliveryRequest $request, $id)
    {
        $user = $request->user(); // 🔔 who updated

        return DB::transaction(function () use ($request, $id, $user) {
            $captiveDelivery = CaptiveDelivery::with('documents')->findOrFail($id);

            $payload  = $request->validated();
            $original = $captiveDelivery->getOriginal();
            $changes  = [];

            // 🧮 diff normal fields (exact same style as other controllers)
            foreach ($payload as $field => $newValue) {
                if (array_key_exists($field, $original)) {
                    $oldValue = $original[$field];

                    if ($oldValue != $newValue) {
                        $changes[$field] = [
                            'old' => $oldValue,
                            'new' => $newValue,
                        ];
                    }
                }
            }

            // 📷 picture diff
            if ($request->hasFile('picture')) {
                $oldPicture = $captiveDelivery->picture;

                if ($oldPicture) {
                    Storage::disk('public')->delete($oldPicture);
                }

                $newPath = $request
                    ->file('picture')
                    ->store('captive_deliveries/pictures', 'public');

                $payload['picture'] = $newPath;

                $changes['picture'] = [
                    'old' => $oldPicture,
                    'new' => $newPath,
                ];
            }

            // 🔄 update main record
            $captiveDelivery->update($payload);

            // ❌ removed documents
            $removedIds = $request->input('removed_document_ids', []);
            if (!empty($removedIds)) {
                $docsToDelete = $captiveDelivery->documents()
                    ->whereIn('id', $removedIds)
                    ->get();

                foreach ($docsToDelete as $doc) {
                    if ($doc->file_path) {
                        Storage::disk('public')->delete($doc->file_path);
                    }
                    $doc->delete();
                }

                $changes['removed_documents'] = [
                    'old' => $removedIds,
                    'new' => [],
                ];
            }

            // ➕ new documents
            if ($request->hasFile('documents')) {
                $newDocsMeta = [];

                foreach ($request->file('documents', []) as $file) {
                    $path = $file->store("captive_deliveries/{$captiveDelivery->id}", 'public');

                    $doc = $captiveDelivery->documents()->create([
                        'file_name'           => $file->getClientOriginalName(),
                        'file_path'           => $path,
                        'mime_type'           => $file->getMimeType(),
                        'size_bytes'          => $file->getSize(),
                        'captive_delivery_id' => $captiveDelivery->id,
                    ]);

                    $newDocsMeta[] = [
                        'id'   => $doc->id,
                        'name' => $doc->file_name,
                    ];
                }

                if (!empty($newDocsMeta)) {
                    $changes['added_documents'] = [
                        'old' => [],
                        'new' => $newDocsMeta,
                    ];
                }
            }

            $captiveDelivery->load('documents');

            // 🔔 Notification: UPDATED (with full changes)
            if ($user) {
                NotificationService::record(
                    $user,
                    module: 'captive_delivery',
                    action: 'updated',
                    title: 'معلومات زندانی تسلیم شده اصلاح شد',
                    message: 'نام زندانی: ' . ($captiveDelivery->name ?? ''),
                    routeName: 'delevirycaptiveform',
                    routeParams: ['id' => $captiveDelivery->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );
            }

            return response()->json([
                'data' => new CaptiveDeliveryResource($captiveDelivery),
            ]);
        });
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user(); // 🔔 who deleted

        return DB::transaction(function () use ($id, $user) {
            $captiveDelivery = CaptiveDelivery::with('documents')->findOrFail($id);
            $name = $captiveDelivery->name;

            // delete main picture
            if ($captiveDelivery->picture) {
                Storage::disk('public')->delete($captiveDelivery->picture);
            }

            // delete documents
            foreach ($captiveDelivery->documents as $doc) {
                if ($doc->file_path) {
                    Storage::disk('public')->delete($doc->file_path);
                }
                $doc->delete();
            }

            $captiveDelivery->delete();

            // 🔔 Notification: DELETED
            if ($user) {
                NotificationService::record(
                    $user,
                    module: 'captive_delivery',
                    action: 'deleted',
                    title: 'زندانی تسلیم شده حذف شد',
                    message: 'نام زندانی: ' . ($name ?? ''),
                    routeName: null,
                    routeParams: [],
                    level: 'warning',
                    changes: null
                );
            }

            return response()->json(['message' => 'Deleted']);
        });
    }
}
