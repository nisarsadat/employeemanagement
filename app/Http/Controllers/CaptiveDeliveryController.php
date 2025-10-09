<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCaptiveDeliveryRequest;
use App\Http\Requests\UpdateCaptiveDeliveryRequest;
use App\Http\Resources\CaptiveDeliveryResource;
use App\Models\CaptiveDelivery;
use App\Models\CaptiveDeliveryDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CaptiveDeliveryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('perPage', 10);
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

            // documents[]
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents', []) as $file) {
                    $path = $file->store("captive_deliveries/{$capt->id}", 'public');
                    CaptiveDeliveryDocument::create([
                        'captive_delivery_id' => $capt->id,
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

    public function show(CaptiveDelivery $captiveDelivery)
    {
        $captiveDelivery->load('documents');
        return response()->json(['data' => new CaptiveDeliveryResource($captiveDelivery)]);
    }

    public function update(UpdateCaptiveDeliveryRequest $request, CaptiveDelivery $captiveDelivery)
    {
        return DB::transaction(function () use ($request, $captiveDelivery) {
            $payload = $request->validated();

            // replace picture only if new provided
            if ($request->hasFile('picture')) {
                // delete old if exists
                if ($captiveDelivery->picture) {
                    Storage::disk('public')->delete($captiveDelivery->picture);
                }
                $payload['picture'] = $request->file('picture')
                    ->store('captive_deliveries/pictures', 'public');
            }

            $captiveDelivery->update($payload);

            // optional new documents on update
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents', []) as $file) {
                    $path = $file->store("captive_deliveries/{$captiveDelivery->id}", 'public');
                    CaptiveDeliveryDocument::create([
                        'captive_delivery_id' => $captiveDelivery->id,
                        'file_name'  => $file->getClientOriginalName(),
                        'file_path'  => $path,
                        'mime_type'  => $file->getMimeType(),
                        'size_bytes' => $file->getSize(),
                    ]);
                }
            }

            $captiveDelivery->load('documents');
            return response()->json(['data' => new CaptiveDeliveryResource($captiveDelivery)]);
        });
    }

    public function destroy(CaptiveDelivery $captiveDelivery)
    {
        // deleting the model cascades to docs; also remove picture file
        if ($captiveDelivery->picture) {
            Storage::disk('public')->delete($captiveDelivery->picture);
        }

        // delete all document files
        foreach ($captiveDelivery->documents as $doc) {
            Storage::disk('public')->delete($doc->file_path);
        }

        $captiveDelivery->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
