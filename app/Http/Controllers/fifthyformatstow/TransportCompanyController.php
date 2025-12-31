<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreTransportCompanyRequest;
use App\Http\Requests\fifthyformatstow\UpdateTransportCompanyRequest;
use App\Http\Resources\fifthyformatstow\TransportCompanyResource;
use App\Models\fifthyformatstow\TransportCompany;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransportCompanyController extends Controller
{
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا ترانسپورټ شرکت مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'دا شرکت مخکې ثبت شوی دی (د شرکت نوم/ډول، د مسؤل معلومات او پته یو شان دي).',
                ],
            ],
        ];
    }

    private function isDuplicateKey(QueryException $e): bool
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

        $q = TransportCompany::query()->with([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('transport_company_name', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('responsible_phone', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%");
            });
        }

        if ($rid) {
            $q->where('resource_id', $rid);
        }

        $q->orderBy($sortBy, $sortDir);

        return TransportCompanyResource::collection($q->paginate($perPage));
    }

    public function store(StoreTransportCompanyRequest $request)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $user) {
                $company = TransportCompany::create($request->validated());

                $company->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'شرکت‌های ترانسپورتی',
                    action: 'created',
                    title: 'شرکت جدید ثبت شد',
                    message: 'نام شرکت: ' . ($company->transport_company_name ?? ''),
                    routeName: 'transport-companies',     // ⚠️ set your real route name
                    routeParams: ['id' => $company->id],
                    level: 'success',
                    changes: null
                );

                return (new TransportCompanyResource($company))
                    ->additional(['message' => 'Created'])
                    ->response()
                    ->setStatusCode(201);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    public function show(TransportCompany $transportCompany)
    {
        $transportCompany->load([
            'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
        ]);

        return TransportCompanyResource::make($transportCompany);
    }

    public function update(UpdateTransportCompanyRequest $request, TransportCompany $transportCompany)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $transportCompany, $user) {
                $validated = $request->validated();

                // 🧮 changes BEFORE update
                $original = $transportCompany->getOriginal();
                $changes  = [];

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                $transportCompany->update($validated);

                $transportCompany->load([
                    'resource' => fn ($qr) => $qr->select('id', 'name', 'code_number', 'phone_number'),
                ]);

                NotificationService::record(
                    $user,
                    module: 'شرکت‌های ترانسپورتی',
                    action: 'updated',
                    title: 'شرکت اصلاح شد',
                    message: 'نام شرکت: ' . ($transportCompany->transport_company_name ?? ''),
                    routeName: 'transport-companies',
                    routeParams: ['id' => $transportCompany->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return TransportCompanyResource::make($transportCompany);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKey($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, TransportCompany $transportCompany)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($transportCompany, $user) {
            $name = $transportCompany->transport_company_name;

            $transportCompany->delete();

            NotificationService::record(
                $user,
                module: 'شرکت‌های ترانسپورتی',
                action: 'deleted',
                title: 'شرکت حذف شد',
                message: 'نام شرکت: ' . ($name ?? ''),
                routeName: null,
                routeParams: ['transport-companies'],
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

            TransportCompany::whereIn('id', $ids)->delete();

            NotificationService::record(
                $user,
                module: 'شرکت‌های ترانسپورتی',
                action: 'deleted',
                title: 'شرکت‌ها به‌صورت گروهی حذف شد',
                message: 'تعداد: ' . count($ids),
                routeName: null,
                routeParams: ['transport-companies'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }
}
