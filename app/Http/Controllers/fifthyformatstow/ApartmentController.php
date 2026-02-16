<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreApartmentRequest;
use App\Http\Requests\fifthyformatstow\UpdateApartmentRequest;
use App\Http\Resources\fifthyformatstow\ApartmentResource;
use App\Models\fifthyformatstow\Apartment;
use App\Services\NotificationService; // 🔔 notifications
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;

class ApartmentController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = trim((string) $request->input('search', ''));

        $q = Apartment::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search !== '') {
            $q->where(function ($qq) use ($search) {
                $qq->where('block_name', 'like', "%{$search}%")
                   ->orWhere('shahrak_name', 'like', "%{$search}%")
                   ->orWhere('responsible_name', 'like', "%{$search}%")
                   ->orWhere('responsible_phone', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%");
            });
        }

        return ApartmentResource::collection($q->paginate($perPage));
    }

    public function store(StoreApartmentRequest $request)
    {
        $user = $request->user(); // 🔔 who did this
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $user, $data) {

                // ✅ picture: file OR string (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    $path = $request->file('picture')->store('uploads/apartments', 'public');
                    $data['picture'] = "storage/{$path}";
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $apartment = Apartment::create($data)
                    ->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: CREATED
                NotificationService::record(
                    $user,
                    module: 'اپارتمان',
                    action: 'created',
                    title: 'اپارتمان جدید ثبت شد',
                    message: 'نام بلاک: ' . ($apartment->block_name ?? ''),
                    routeName: 'apartments', // ⚠️ adjust if your route name differs
                    routeParams: ['id' => $apartment->id],
                    level: 'success',
                    changes: null
                );

                return ApartmentResource::make($apartment);
            });
        } catch (QueryException $e) {
            // ✅ Duplicate entry (MySQL error code 1062)
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                $field = $this->guessDuplicateField($e->getMessage());

                return response()->json([
                    'message' => 'Duplicate value. This field must be unique.',
                    'errors'  => [
                        $field => ['This value already exists. Please use another value.'],
                    ],
                ], 422);
            }

            throw $e;
        }
    }

    public function show(Apartment $apartment)
    {
        $apartment->load(['resource:id,name,code_number,phone_number']);
        return ApartmentResource::make($apartment);
    }

    public function update(UpdateApartmentRequest $request, Apartment $apartment)
    {
        $user = $request->user(); // 🔔
        $data = $request->validated();

        try {
            return DB::transaction(function () use ($request, $apartment, $user, $data) {
                // 🧮 build changes BEFORE update
                $original = $apartment->getOriginal();
                $changes  = [];

                foreach ($data as $field => $newValue) {
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

                // ✅ picture: replace file if uploaded (same style as SaeenFasad)
                if ($request->hasFile('picture')) {
                    if ($apartment->picture && str_starts_with($apartment->picture, 'storage/')) {
                        $old = str_replace('storage/', '', $apartment->picture);
                        Storage::disk('public')->delete($old);
                    }

                    $path = $request->file('picture')->store('uploads/apartments', 'public');
                    $data['picture'] = "storage/{$path}";

                    $changes['picture'] = [
                        'old' => $original['picture'] ?? null,
                        'new' => $data['picture'],
                    ];
                } else {
                    if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                        unset($data['picture']);
                    }
                }

                $apartment->update($data);
                $apartment->load(['resource:id,name,code_number,phone_number']);

                // 🔔 Notification: UPDATED + changes
                NotificationService::record(
                    $user,
                    module: 'اپارتمان',
                    action: 'updated',
                    title: 'اپارتمان اصلاح شد',
                    message: 'نام بلاک: ' . ($apartment->block_name ?? ''),
                    routeName: 'apartments', // ⚠️ adjust if needed
                    routeParams: ['id' => $apartment->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return ApartmentResource::make($apartment);
            });
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                $field = $this->guessDuplicateField($e->getMessage());

                return response()->json([
                    'message' => 'Duplicate value. This field must be unique.',
                    'errors'  => [
                        $field => ['This value already exists. Please use another value.'],
                    ],
                ], 422);
            }

            throw $e;
        }
    }

    public function destroy(Request $request, Apartment $apartment)
    {
        $user = $request->user(); // 🔔

        return DB::transaction(function () use ($apartment, $user) {
            $blockName = $apartment->block_name;

            // ✅ delete picture file (same style as SaeenFasad)
            if ($apartment->picture && str_starts_with($apartment->picture, 'storage/')) {
                $old = str_replace('storage/', '', $apartment->picture);
                Storage::disk('public')->delete($old);
            }

            $apartment->delete();

            // 🔔 Notification: DELETED
            NotificationService::record(
                $user,
                module: 'اپارتمان',
                action: 'deleted',
                title: 'اپارتمان حذف شد',
                message: 'نام بلاک: ' . ($blockName ?? ''),
                routeName: null,
                routeParams: ['apartments'],
                level: 'warning',
                changes: null
            );

            return response()->noContent();
        });
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        // Optional: if you want to delete picture files for bulk delete,
        // you must loop and delete each picture first (like SaeenFasad).
        // (I kept your original logic untouched.)
        Apartment::destroy($ids);

        return response()->noContent();
    }

    /**
     * Try to detect which field caused the UNIQUE constraint error.
     */
    private function guessDuplicateField(string $errorMessage): string
    {
        $map = [
            'apartments_block_name_unique' => 'block_name',
            'apartments_apartments_quantity_unique' => 'apartments_quantity',
            'apartments_shahrak_name_unique' => 'shahrak_name',

            'apartments_responsible_name_unique' => 'responsible_name',
            'apartments_responsible_f_name_unique' => 'responsible_f_name',
            'apartments_responsible_position_unique' => 'responsible_position',
            'apartments_responsible_phone_unique' => 'responsible_phone',

            'apartments_province_unique' => 'province',
            'apartments_zone_unique' => 'zone',
            'apartments_district_unique' => 'district',
        ];

        foreach ($map as $indexName => $field) {
            if (str_contains($errorMessage, $indexName)) {
                return $field;
            }
        }

        return 'block_name'; // safe fallback
    }
}
