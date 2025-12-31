<?php

namespace App\Http\Controllers\fifthyformatsthree;

use App\Http\Controllers\Controller;
use App\Models\fifthyformatsthree\Badmashano;
use App\Http\Resources\fifthyformatsthree\BadmashanoResource;
use App\Http\Requests\fifthyformatsthree\StoreBadmashanoRequest;
use App\Http\Requests\fifthyformatsthree\UpdateBadmashanoRequest;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class BadmashanoController extends Controller
{
    private function isDuplicateKey(QueryException $e): bool
    {
        // MySQL duplicate key
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    private function duplicateResponse()
    {
        return response()->json([
            'message' => 'دا ریکارډ مخکې ثبت شوی دی.',
            'errors'  => [
                'duplicate' => [
                    'د همدې معلوماتو سره (لقب/نوم/د پلار نوم او اصلي ادرس) ریکارډ مخکې له مخکې موجود دی.',
                ],
            ],
        ], 422);
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Badmashano::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('nickname', 'like', "%{$search}%")
                   ->orWhere('father_name', 'like', "%{$search}%");
            });
        }

        return BadmashanoResource::collection($q->paginate($perPage));
    }

    public function store(StoreBadmashanoRequest $request)
    {
        $user = $request->user(); // 🔔 who did this

        try {
            $item = Badmashano::create($request->validated())
                ->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: CREATED
            NotificationService::record(
                $user,
                module: 'بدماشانو',
                action: 'created',
                title: 'نوی بدماش ثبت شو',
                message: 'نوم: ' . ($item->name ?? ''),
                routeName: 'badmashanos',          // ⚠️ set to your real route name
                routeParams: ['id' => $item->id],
                level: 'success',
                changes: null
            );

            return BadmashanoResource::make($item);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function show(Badmashano $badmashano)
    {
        $badmashano->load(['resource:id,name,code_number,phone_number']);
        return BadmashanoResource::make($badmashano);
    }

    public function update(UpdateBadmashanoRequest $request, Badmashano $badmashano)
    {
        $user = $request->user(); // 🔔

        try {
            // 🧮 build changes BEFORE update
            $original  = $badmashano->getOriginal();
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

            $badmashano->update($validated);
            $badmashano->load(['resource:id,name,code_number,phone_number']);

            // 🔔 Notification: UPDATED
            NotificationService::record(
                $user,
                module: 'بدماشانو',
                action: 'updated',
                title: 'بدماش اصلاح شو',
                message: 'نوم: ' . ($badmashano->name ?? ''),
                routeName: 'badmashanos',          // ⚠️ set to your real route name
                routeParams: ['id' => $badmashano->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return BadmashanoResource::make($badmashano);
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return $this->duplicateResponse();
            }
            throw $e;
        }
    }

    public function destroy(Request $request, Badmashano $badmashano)
    {
        $user = $request->user(); // 🔔

        $name = $badmashano->name;

        $badmashano->delete();

        // 🔔 Notification: DELETED
        NotificationService::record(
            $user,
            module: 'بدماشانو',
            action: 'deleted',
            title: 'بدماش حذف شو',
            message: 'نوم: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['badmashanos'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = (array) $request->input('ids', []);
        Badmashano::destroy($ids);

        // 🔔 Notification: BULK DELETED
        NotificationService::record(
            $user,
            module: 'بدماشانو',
            action: 'bulk_deleted',
            title: 'ډله‌ییز حذف ترسره شو',
            message: 'حذف شوي ریکارډونه: ' . count($ids),
            routeName: null,
            routeParams: ['badmashanos'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
}
