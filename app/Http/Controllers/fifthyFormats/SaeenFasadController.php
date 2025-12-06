<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreSaeenFasadRequest;
use App\Http\Requests\fifthyFormats\UpdateSaeenFasadRequest;
use App\Http\Resources\fifthyFormats\SaeenFasadResource;
use App\Models\fifthyFormats\SaeenFasad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SaeenFasadController extends Controller
{
    /**
     * GET /saeen-fasadas
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 15);
        $search  = $request->input('search');

        $q = SaeenFasad::query()->with('resource');

        if ($search) {
            // keep it simple like Fasad (you can extend to other fields if you want)
            $q->where('name', 'like', "%{$search}%");
        }

        return SaeenFasadResource::collection($q->paginate($perPage));
    }

    /**
     * GET /saeen-fasadas/{saeenFasad}
     */
    public function show(SaeenFasad $saeenFasad)
    {
        $saeenFasad->load('resource');
        return new SaeenFasadResource($saeenFasad);
    }

    /**
     * POST /saeen-fasadas
     * Accepts `picture` either as a FILE or a STRING path/url.
     */
    public function store(StoreSaeenFasadRequest $request)
    {
        $data = $request->validated();

        // If picture is uploaded as a file, store it and save the string path.
        if ($request->hasFile('picture')) {
            $path = $request->file('picture')->store('uploads/saeen-fasadas', 'public'); // e.g. uploads/saeen-fasadas/xxx.jpg
            $data['picture'] = "storage/{$path}";                                        // save string like: storage/uploads/saeen-fasadas/xxx.jpg
        } else {
            // If validator allowed 'picture' and it's not a string, drop it to avoid "must be a string" errors
            if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                unset($data['picture']);
            }
        }

        $item = SaeenFasad::create($data);
        $item->load('resource');

        return (new SaeenFasadResource($item))
            ->additional(['message' => 'Created'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * PUT /saeen-fasadas/{saeenFasad}
     * Accepts `picture` either as a FILE or a STRING; replaces file if a new one is uploaded.
     */
    public function update(UpdateSaeenFasadRequest $request, SaeenFasad $saeenFasad)
    {
        $data = $request->validated();

        if ($request->hasFile('picture')) {
            // Delete old stored picture if we had one saved under /storage
            if ($saeenFasad->picture && str_starts_with($saeenFasad->picture, 'storage/')) {
                $old = str_replace('storage/', '', $saeenFasad->picture);
                Storage::disk('public')->delete($old);
            }

            $path = $request->file('picture')->store('uploads/saeen-fasadas', 'public');
            $data['picture'] = "storage/{$path}";
        } else {
            // If picture is not a string (e.g. null/object), don't override with invalid type
            if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                unset($data['picture']);
            }
        }

        $saeenFasad->update($data);
        $saeenFasad->load('resource');

        return new SaeenFasadResource($saeenFasad);
    }

    /**
     * DELETE /saeen-fasadas/{saeenFasad}
     */
    public function destroy(SaeenFasad $saeenFasad)
    {
        try {
            // ✅ Remove the stored file if it lives under /storage
            if ($saeenFasad->picture && str_starts_with($saeenFasad->picture, 'storage/')) {
                $old = str_replace('storage/', '', $saeenFasad->picture);
                Storage::disk('public')->delete($old);
            }

            $saeenFasad->delete();

            return response()->json([
                'message' => 'Deleted',
            ], 200);
        } catch (\Throwable $e) {
            // Log the error for debugging
            report($e);

            return response()->json([
                'message' => 'Unable to delete record.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
