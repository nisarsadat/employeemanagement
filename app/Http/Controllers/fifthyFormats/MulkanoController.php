<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreMulkanoRequest;
use App\Http\Requests\fifthyFormats\UpdateMulkanoRequest;
use App\Http\Resources\fifthyFormats\MulkanoResource;
use App\Models\fifthyFormats\Mulkano;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MulkanoController extends Controller
{
    /**
     * GET /mulkanos
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 15);
        $search  = $request->get('search');
        $rid     = $request->get('resource_id');

        $sortBy  = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        $q = Mulkano::query()->with([
            'resource' => function ($qr) {
                $qr->select('id', 'name', 'code_number', 'phone_number');
            }
        ]);

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                   ->orWhere('father_name', 'like', "%{$search}%")
                   ->orWhere('grand_father_name', 'like', "%{$search}%")
                   ->orWhere('phone_number', 'like', "%{$search}%")
                   ->orWhere('specific_place', 'like', "%{$search}%");
            });
        }

        if ($rid) {
            $q->where('resource_id', $rid);
        }

        $q->orderBy($sortBy, $sortDir);

        return MulkanoResource::collection($q->paginate($perPage));
    }

    /**
     * GET /mulkanos/{mulkano}
     */
    public function show(Mulkano $mulkano)
    {
        $mulkano->load([
            'resource' => function ($qr) {
                $qr->select('id', 'name', 'code_number', 'phone_number');
            }
        ]);

        return new MulkanoResource($mulkano);
    }

    /**
     * POST /mulkanos
     * Accepts picture either as an uploaded file OR a string path/url.
     */
    public function store(StoreMulkanoRequest $request)
    {
        $data = $request->validated();

        // If a file is uploaded, store it and overwrite picture with the stored path
        if ($request->hasFile('picture')) {
            $path = $request->file('picture')->store('uploads/mulkanos', 'public');
            $data['picture'] = "storage/{$path}";
        } else {
            // If picture exists but isn't a string, drop it to avoid "must be a string" errors
            if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                unset($data['picture']);
            }
        }

        $mulkano = Mulkano::create($data);
        $mulkano->load([
            'resource' => function ($qr) {
                $qr->select('id', 'name', 'code_number', 'phone_number');
            }
        ]);

        return (new MulkanoResource($mulkano))
            ->additional(['message' => 'Created'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * PUT /mulkanos/{mulkano}
     * Handles replacing picture file; keeps old string if none provided.
     */
    public function update(UpdateMulkanoRequest $request, Mulkano $mulkano)
    {
        $data = $request->validated();

        // If a new file is uploaded, delete old (if it was on public disk) and store new
        if ($request->hasFile('picture')) {
            if ($mulkano->picture && str_starts_with($mulkano->picture, 'storage/')) {
                $oldDiskPath = str_replace('storage/', '', $mulkano->picture);
                Storage::disk('public')->delete($oldDiskPath);
            }
            $path = $request->file('picture')->store('uploads/mulkanos', 'public');
            $data['picture'] = "storage/{$path}";
        } else {
            // If no new file: only accept string picture; otherwise keep existing value
            if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                unset($data['picture']);
            }
        }

        $mulkano->update($data);
        $mulkano->load([
            'resource' => function ($qr) {
                $qr->select('id', 'name', 'code_number', 'phone_number');
            }
        ]);

        return new MulkanoResource($mulkano);
    }

    /**
     * DELETE /mulkanos/{mulkano}
     */
    public function destroy(Mulkano $mulkano)
    {
        // Optional: remove stored file from disk
        if ($mulkano->picture && str_starts_with($mulkano->picture, 'storage/')) {
            $oldDiskPath = str_replace('storage/', '', $mulkano->picture);
            Storage::disk('public')->delete($oldDiskPath);
        }

        $mulkano->delete();

        return response()->noContent();
    }
}
