<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaptiveDeliveryRequest;
use App\Http\Requests\UpdateCaptiveDeliveryRequest;
use App\Http\Resources\CaptiveDeliveryResource;
use App\Models\CaptiveDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        return DB::transaction(function () use ($request) {
            $payload = $request->validated();

            // picture upload
            if ($request->hasFile('picture')) {
                $payload['picture'] = $request->file('picture')
                    ->store('captive_deliveries/pictures', 'public');
            }

            $capt = CaptiveDelivery::create($payload);

            // new documents[]
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents', []) as $file) {
                    $path = $file->store("captive_deliveries/{$capt->id}", 'public');

                    $capt->documents()->create([
                        'file_name'  => $file->getClientOriginalName(),
                        'file_path'  => $path,
                        'mime_type'  => $file->getMimeType(),
                        'size_bytes' => $file->getSize(),
                    ]);
                }
            }

            $capt->load('documents');

            return response()->json(['data' => new CaptiveDeliveryResource($capt)], 201);
        });
    }

    public function show($id)
    {
        // Always load model manually
        $captiveDelivery = CaptiveDelivery::with('documents')->findOrFail($id);

        return response()->json([
            'data' => new CaptiveDeliveryResource($captiveDelivery),
        ]);
    }

    public function update(UpdateCaptiveDeliveryRequest $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {
            // 🔹 ALWAYS load the model manually, don't trust binding right now
            $captiveDelivery = CaptiveDelivery::with('documents')->findOrFail($id);

            $payload = $request->validated();

            // 🔹 Handle picture (optional)
            if ($request->hasFile('picture')) {
                if ($captiveDelivery->picture) {
                    Storage::disk('public')->delete($captiveDelivery->picture);
                }

                $payload['picture'] = $request
                    ->file('picture')
                    ->store('captive_deliveries/pictures', 'public');
            }

            // 🔹 Update main record
            $captiveDelivery->update($payload);

            // 🔹 Optionally delete removed documents
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
            }

            // 🔹 Handle NEW documents on update
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents', []) as $file) {
                    // 👇 NOW id is guaranteed, so folder is ok
                    $path = $file->store("captive_deliveries/{$captiveDelivery->id}", 'public');

                    // You can use relation:
                    $captiveDelivery->documents()->create([
                        'file_name'          => $file->getClientOriginalName(),
                        'file_path'          => $path,
                        'mime_type'          => $file->getMimeType(),
                        'size_bytes'         => $file->getSize(),
                        // 👇 not required because relation fills it, but safe:
                        'captive_delivery_id' => $captiveDelivery->id,
                    ]);
                }
            }

            $captiveDelivery->load('documents');

            return response()->json([
                'data' => new CaptiveDeliveryResource($captiveDelivery),
            ]);
        });
    }

   public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            // 🔹 Manually load the record + documents, same as in show/update
            $captiveDelivery = CaptiveDelivery::with('documents')->findOrFail($id);

            // 🔹 Delete main picture
            if ($captiveDelivery->picture) {
                Storage::disk('public')->delete($captiveDelivery->picture);
            }

            // 🔹 Delete documents files
            foreach ($captiveDelivery->documents as $doc) {
                if ($doc->file_path) {
                    Storage::disk('public')->delete($doc->file_path);
                }
                $doc->delete();
            }

            // 🔹 Finally delete the main record
            $captiveDelivery->delete();

            return response()->json(['message' => 'Deleted']);
        });
    }

}
