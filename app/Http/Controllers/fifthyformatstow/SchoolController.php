<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreSchoolRequest;
use App\Http\Requests\fifthyformatstow\UpdateSchoolRequest;
use App\Http\Resources\fifthyformatstow\SchoolResource;
use App\Models\fifthyformatstow\School;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SchoolController extends Controller
{
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا ښوونځی مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'دا ښوونځی مخکې ثبت شوی دی (د ښوونځي نوم/ډول، د مدیر معلومات او پته یو شان دي).',
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

        $q = School::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('school_name', 'like', "%{$search}%")
                   ->orWhere('principle_name', 'like', "%{$search}%")
                   ->orWhere('principle_phone', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%");
            });
        }

        if ($rid) {
            $q->where('resource_id', $rid);
        }

        $q->orderBy($sortBy, $sortDir);

        return SchoolResource::collection($q->paginate($perPage));
    }

    public function store(StoreSchoolRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $user) {
                $school = School::create($request->validated());

                $school->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'مکاتب',
                    action: 'created',
                    title: 'مکتب جدید ثبت شد',
                    message: 'نام مکتب: ' . ($school->school_name ?? ''),
                    routeName: 'schools',                 // ⚠️ set your real route name
                    routeParams: ['id' => $school->id],
                    level: 'success',
                    changes: null
                );

                return (new SchoolResource($school))
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

    public function show(School $school)
    {
        $school->load([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        return SchoolResource::make($school);
    }

    public function update(UpdateSchoolRequest $request, School $school)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $school, $user) {
                $validated = $request->validated();

                // 🧮 changes BEFORE update
                $original = $school->getOriginal();
                $changes  = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                $school->update($validated);

                $school->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'مکاتب',
                    action: 'updated',
                    title: 'مکتب اصلاح شد',
                    message: 'نام مکتب: ' . ($school->school_name ?? ''),
                    routeName: 'schools',                 // ⚠️ set your real route name
                    routeParams: ['id' => $school->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return SchoolResource::make($school);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, School $school)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($school, $user) {
            $name = $school->school_name;

            $school->delete();

            NotificationService::record(
                $user,
                module: 'مکاتب',
                action: 'deleted',
                title: 'مکتب حذف شد',
                message: 'نام مکتب: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['schools'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    public function bulkDelete(Request $request)
    {
        $user = $request->user(); // 🔔

        $ids = $request->input('ids', []);
        $ids = is_array($ids) ? array_values(array_filter($ids)) : [];

        return DB::transaction(function () use ($ids, $user) {
            if (empty($ids)) {
                return response()->noContent();
            }

            School::whereIn('id', $ids)->delete();

            NotificationService::record(
                $user,
                module: 'مکاتب',
                action: 'deleted',
                title: 'مکاتب به‌صورت گروهی حذف شد',
                message: 'تعداد: ' . count($ids),
                routeName: null,
                routeParams: ['schools'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}
