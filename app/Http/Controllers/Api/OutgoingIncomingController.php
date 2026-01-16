<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOutgoingIncomingRequest;
use App\Http\Requests\UpdateOutgoingIncomingRequest;
use App\Http\Resources\OutgoingIncomingResource;
use App\Models\OutgoingIncoming;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Services\NotificationService; // 🔔 notifications

class OutgoingIncomingController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 25);
        $search  = $request->get('search');

        $query = OutgoingIncoming::query()
            ->search($search)
            ->latest('id');

        return OutgoingIncomingResource::collection(
            $query->paginate($perPage)
        );
    }

    public function store(StoreOutgoingIncomingRequest $request)
    {
        $user = $request->user(); // 🔔 who did this

        try {
            return DB::transaction(function () use ($request, $user) {
                $row = OutgoingIncoming::create($request->validated());

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'صادره/وارده',
                    action: 'created',
                    title: 'نوی ریکارډ ثبت شو',
                    message: 'مرجع: ' . ($row->marja ?? ''),
                    routeName: 'outgoing-incomings',      // ⚠️ adjust if needed
                    routeParams: ['id' => $row->id],
                    level: 'success',
                    changes: null
                );

                return OutgoingIncomingResource::make($row);
            });
        } catch (QueryException $e) {
            // ✅ Duplicate unique constraint (MySQL: 1062)
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'message' => 'دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).',
                    'errors'  => [
                        'duplicate' => ['دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).'],
                    ],
                ], 422);
            }

            throw $e;
        }
    }

    public function show(OutgoingIncoming $outgoingIncoming)
    {
        return OutgoingIncomingResource::make($outgoingIncoming);
    }

    public function update(UpdateOutgoingIncomingRequest $request, OutgoingIncoming $outgoingIncoming)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $outgoingIncoming, $user) {
                // 🧮 build changes BEFORE update
                $original  = $outgoingIncoming->getOriginal();
                $validated = $request->validated();
                $changes   = [];

                foreach ($validated as $field => $newValue) {
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

                // 🔄 apply update
                $outgoingIncoming->update($validated);

                // 🔔 Notification: UPDATED + changes
                NotificationService::record(
                    $user,
                    module: 'صادره/وارده',
                    action: 'updated',
                    title: 'ریکارډ اصلاح شو',
                    message: 'مرجع: ' . ($outgoingIncoming->marja ?? ''),
                    routeName: 'outgoing-incomings',      // ⚠️ adjust if needed
                    routeParams: ['id' => $outgoingIncoming->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return OutgoingIncomingResource::make($outgoingIncoming);
            });
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'message' => 'دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).',
                    'errors'  => [
                        'duplicate' => ['دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).'],
                    ],
                ], 422);
            }

            throw $e;
        }
    }

    public function destroy(Request $request, OutgoingIncoming $outgoingIncoming)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($outgoingIncoming, $user) {
            $marja = $outgoingIncoming->marja;

            $outgoingIncoming->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'صادره/وارده',
                action: 'deleted',
                title: 'ریکارډ حذف شو',
                message: 'مرجع: ' . ($marja ?? ''),
                routeName: null,
                routeParams: ['outgoing-incomings'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}
