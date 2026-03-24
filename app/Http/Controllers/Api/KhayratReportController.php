<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKhayratReportRequest;
use App\Http\Requests\UpdateKhayratReportRequest;
use App\Http\Resources\KhayratReportResource;
use App\Models\KhayratReport;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KhayratReportController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 15);
        $search  = trim((string) $request->input('search', ''));

        $query = KhayratReport::query()
            ->search($search)
            ->with([
                // ✅ normal eager load like OfficeEmployee
                'resource:id,name,code_number,phone_number',
                'officeEmployee:id,name,code_number',
            ])
            ->latest('id');

        return KhayratReportResource::collection(
            $query->paginate($perPage)
        );
    }

    public function store(StoreKhayratReportRequest $request)
    {
        $user = $request->user(); // 🔔 who did this

        return DB::transaction(function () use ($request, $user) {
            $item = KhayratReport::create($request->validated());

            $item->load([
                'resource:id,name,code_number,phone_number',
                'officeEmployee:id,name,code_number',
            ]);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'د خیریت راپور',
                action: 'created',
                title: 'د خیریت راپور ثبت شوو',
                message: 'کوډ: ' . ($item->id ?? ''),
                routeName: 'KhayratReportsIndex',          // ⚠️ set to your frontend route name
                routeParams: ['id' => $item->id],
                level: 'success',
                changes: null
            );

            return new KhayratReportResource($item);
        });
    }

    public function show(KhayratReport $khayratReport)
    {
        $khayratReport->load([
            'resource:id,name,code_number,phone_number',
            'officeEmployee:id,name,code_number',
        ]);

        return new KhayratReportResource($khayratReport);
    }

    public function update(UpdateKhayratReportRequest $request, KhayratReport $khayratReport)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($request, $khayratReport, $user) {
            // 🧮 build changes BEFORE update
            $original  = $khayratReport->getOriginal();
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
            $khayratReport->update($validated);

            $khayratReport->load([
                'resource:id,name,code_number,phone_number',
                'officeEmployee:id,name,code_number',
            ]);

            // 🔔 Notification: UPDATED + changes
            NotificationService::record(
                $user,
                module: 'د خیریت راپور',
                action: 'updated',
                title: 'د خیریت راپور اصلاح شوو',
                message: 'کوډ: ' . ($khayratReport->id ?? ''),
                routeName: 'KhayratReportsIndex',          // ⚠️ set to your frontend route name
                routeParams: ['id' => $khayratReport->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return new KhayratReportResource($khayratReport);
        });
    }

    public function destroy(Request $request, KhayratReport $khayratReport)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($khayratReport, $user) {
            $id = $khayratReport->id;

            $khayratReport->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'د خیریت راپور',
                action: 'deleted',
                title: 'د خیریت راپور حذف شوو',
                message: 'کوډ: ' . ($id ?? ''),
                routeName: null,
                routeParams: ['KhayratReportsIndex'],
                level: 'warning',
                changes: null
            );

            return response()->json(['message' => 'Deleted']);
        });
    }
}
