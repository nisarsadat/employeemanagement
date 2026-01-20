<?php

namespace App\Http\Controllers\fifthyformatone;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatone\StoreNonResponsablePeopleRequest;
use App\Http\Requests\fifthyformatone\UpdateNonResponsablePeopleRequest;
use App\Http\Resources\fifthyformatone\NonResponsablePeopleResource;
use App\Models\fifthyformatone\NonResponsablePeople;
use App\Services\NotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NonResponsablePeopleController extends Controller
{
    /**
     * Pashto duplicate error
     */
    private function duplicateErrorPashto(): array
    {
        return [
            'message' => 'دا کس مخکې ثبت شوی دی. مهرباني وکړئ تکراري معلومات مه داخلوی.',
            'errors'  => [
                'duplicate' => [
                    'همدا نوم، د پلار نوم او اصلي پته مخکې ثبت شوې ده.',
                ],
            ],
        ];
    }

    private function isDuplicateKeyException(QueryException $e): bool
    {
        return (int) ($e->errorInfo[1] ?? 0) === 1062;
    }

    /** GET /non-responsable-people */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 15);
        $search  = $request->get('search');

        $q = NonResponsablePeople::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                  ->orWhere('nick_name', 'like', "%{$search}%")
                  ->orWhere('father_name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('old_job', 'like', "%{$search}%")
                  ->orWhere('now_job', 'like', "%{$search}%");
            });
        }

        return NonResponsablePeopleResource::collection($q->paginate($perPage));
    }

    /** POST /non-responsable-people */
    public function store(StoreNonResponsablePeopleRequest $request)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $user) {
                $validated = $request->validated();

                // ✅ handle picture upload
                if ($request->hasFile('picture')) {
                    $validated['picture'] = $request->file('picture')
                        ->store('non_responsable_people', 'public');
                }

                $record = NonResponsablePeople::create($validated);

                $record->load(['resource:id,name,code_number,phone_number']);

                NotificationService::record(
                    $user,
                    module: 'اشخاص غیرمسؤول',
                    action: 'created',
                    title: 'شخص جدید ثبت شد',
                    message: 'نام: ' . ($record->name ?? ''),
                    routeName: 'non-responsable-people',
                    routeParams: ['id' => $record->id],
                    level: 'success',
                    changes: null
                );

                return (new NonResponsablePeopleResource($record))
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

    /** GET /non-responsable-people/{id} */
    public function show($id)
    {
        $record = NonResponsablePeople::with(['resource:id,name,code_number,phone_number'])
            ->findOrFail($id);

        return NonResponsablePeopleResource::make($record);
    }

    /** PUT /non-responsable-people/{id} */
    public function update(UpdateNonResponsablePeopleRequest $request, $id)
    {
        $user = $request->user();

        try {
            return DB::transaction(function () use ($request, $id, $user) {
                $record = NonResponsablePeople::findOrFail($id);

                $validated = $request->validated();
                $original  = $record->getOriginal();
                $changes   = [];

                // ✅ handle picture upload (replace + delete old)
                if ($request->hasFile('picture')) {
                    if ($record->picture) {
                        Storage::disk('public')->delete($record->picture);
                    }
                    $validated['picture'] = $request->file('picture')
                        ->store('non_responsable_people', 'public');
                }

                foreach ($validated as $field => $newValue) {
                    if (array_key_exists($field, $original) && $original[$field] != $newValue) {
                        $changes[$field] = [
                            'old' => $original[$field],
                            'new' => $newValue,
                        ];
                    }
                }

                $record->update($validated);
                $record->load(['resource:id,name,code_number,phone_number']);

                NotificationService::record(
                    $user,
                    module: 'اشخاص غیرمسؤول',
                    action: 'updated',
                    title: 'شخص اصلاح شد',
                    message: 'نام: ' . ($record->name ?? ''),
                    routeName: 'non-responsable-people',
                    routeParams: ['id' => $record->id],
                    level: 'info',
                    changes: !empty($changes) ? $changes : null
                );

                return NonResponsablePeopleResource::make($record);
            });
        } catch (QueryException $e) {
            if ($this->isDuplicateKeyException($e)) {
                return response()->json($this->duplicateErrorPashto(), 422);
            }
            throw $e;
        }
    }

    /** DELETE /non-responsable-people/{id} */
    public function destroy(Request $request, $id)
    {
        $user   = $request->user();
        $record = NonResponsablePeople::findOrFail($id);
        $name   = $record->name;

        // ✅ delete picture file too
        if ($record->picture) {
            Storage::disk('public')->delete($record->picture);
        }

        $record->delete();

        NotificationService::record(
            $user,
            module: 'اشخاص غیرمسؤول',
            action: 'deleted',
            title: 'شخص حذف شد',
            message: 'نام: ' . ($name ?? ''),
            routeName: null,
            routeParams: ['non-responsable-people'],
            level: 'warning',
            changes: null
        );

        return response()->noContent();
    }
}
