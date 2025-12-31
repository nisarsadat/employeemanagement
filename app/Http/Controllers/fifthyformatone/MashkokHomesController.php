<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreMashkokHomesRequest;
use App\Http\Requests\fifthyformatone\UpdateMashkokHomesRequest;
use App\Http\Resources\fifthyformatone\MashkokHomesResource;
use App\Models\fifthyformatone\MashkokHomes;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MashkokHomesController extends Controller
{
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا کور مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'دا موقعیت مخکې ثبت شوی دی (همدا ولایت، زون، ولسوالۍ، کلی او مشخص ځای).',
                ],
            ],
        ];
    }

    private function isDuplicateKeyException(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');
        $rid     = $request->input('resource_id');

        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = in_array($request->input('sort_dir'), ['asc', 'desc'], true) ? $request->input('sort_dir') : 'desc';

        $q = MashkokHomes::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        if ($search) {
            $q->where(function ($w) use ($search) {
                $w->where('province', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('village', 'like', "%{$search}%")
                  ->orWhere('home_type', 'like', "%{$search}%");
            });
        }

        if ($rid) {
            $q->where('resource_id', $rid);
        }

        $q->orderBy($sortBy, $sortDir);

        return MashkokHomesResource::collection($q->paginate($perPage));
    }

    public function store(StoreMashkokHomesRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $user) {
                $record = MashkokHomes::create($request->validated());

                $record->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'خانه‌های مشکوک',
                    action: 'created',
                    title: 'خانه مشکوک جدید ثبت شد',
                    message: 'محل: ' . ($record->province ?? '') . ' - ' . ($record->district ?? '') . ' - ' . ($record->village ?? ''),
                    routeName: 'mashkok-homes',            // ⚠️ set your real route name
                    routeParams: ['id' => $record->id],
                    level: 'success',
                    changes: null
                );

                return (new MashkokHomesResource($record))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    public function show(MashkokHomes $mashkok_home)
    {
        $mashkok_home->load([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        return MashkokHomesResource::make($mashkok_home);
    }

    public function update(UpdateMashkokHomesRequest $request, MashkokHomes $mashkok_home)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $mashkok_home, $user) {
                $validated = $request->validated();

                // 🧮 changes BEFORE update
                $original = $mashkok_home->getOriginal();
                $changes  = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                $mashkok_home->update($validated);

                $mashkok_home->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'خانه‌های مشکوک',
                    action: 'updated',
                    title: 'خانه مشکوک اصلاح شد',
                    message: 'محل: ' . ($mashkok_home->province ?? '') . ' - ' . ($mashkok_home->district ?? '') . ' - ' . ($mashkok_home->village ?? ''),
                    routeName: 'mashkok-homes',            // ⚠️ set your real route name
                    routeParams: ['id' => $mashkok_home->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return MashkokHomesResource::make($mashkok_home);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }
}
