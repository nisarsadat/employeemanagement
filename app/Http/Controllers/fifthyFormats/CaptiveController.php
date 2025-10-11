<?php

namespace App\Http\Controllers\fifthyFormats;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyFormats\StoreCaptiveRequest;
use App\Http\Requests\fifthyFormats\UpdateCaptiveRequest;
use App\Http\Resources\fifthyFormats\CaptiveResource;
use App\Models\fifthyFormats\Captive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CaptiveController extends Controller
{
    /**
     * GET /captives
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 15);
        $search  = $request->get('search');
        $rid     = $request->get('resource_id');

        // Whitelist sortable columns to avoid SQL injection / typos
        $sortable = ['id', 'name', 'created_at', 'updated_at'];
        $sortBy   = in_array($request->get('sort_by', 'created_at'), $sortable, true)
            ? $request->get('sort_by', 'created_at')
            : 'created_at';

        $sortDir  = $request->get('sort_dir', 'desc');
        if (!in_array(strtolower($sortDir), ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

    $q = Captive::query()
        ->leftJoin('resources', 'captives.resource_id', '=', 'resources.id')
        ->select([
            'captives.*',
            'resources.code_number as resource_code',
            'resources.phone_number as resource_phone_number',
        ])
        ->with([
            'resource' => function ($qr) {
                $qr->select('id', 'name', 'code_number', 'phone_number');
            }
        ]);


        if ($search) {
            $q->where('name', 'like', "%{$search}%");
        }

        if ($rid) {
            $q->where('resource_id', $rid);
        }

        $q->orderBy($sortBy, $sortDir);

        return CaptiveResource::collection($q->paginate($perPage));
    }

    /**
     * GET /captives/{captive}
     */
    public function show(Captive $captive)
    {
        $captive->load([
            'resource' => function ($qr) {
                $qr->select('id', 'name', 'code_number', 'phone_number');
            }
        ]);

        return new CaptiveResource($captive);
    }

    /**
     * POST /captives
     * Accepts picture either as an uploaded file OR a string path/url.
     */
    public function store(StoreCaptiveRequest $request)
    {
        $data = $request->validated();

        // If a file is uploaded, store it and overwrite picture with the stored path
        if ($request->hasFile('picture')) {
            $path = $request->file('picture')->store('uploads/captives', 'public'); // e.g. uploads/captives/xxx.jpg
            $data['picture'] = "storage/{$path}"; // public URL path
        } else {
            // If picture exists but isn't a string, drop it to avoid "must be a string" errors
            if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                unset($data['picture']);
            }
        }

        $captive = Captive::create($data);

        // reload with consistent resource select
        $captive->load([
            'resource' => function ($qr) {
                $qr->select('id', 'name', 'code_number', 'phone_number');
            }
        ]);

        return (new CaptiveResource($captive))
            ->additional(['message' => 'Created'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * PUT /captives/{captive}
     * Handles replacing picture file; keeps old string if none provided.
     */
    public function update(UpdateCaptiveRequest $request, Captive $captive)
    {
        $data = $request->validated();

        // If a new file is uploaded, delete old (if it was on public disk) and store new
        if ($request->hasFile('picture')) {
            if ($captive->picture && str_starts_with($captive->picture, 'storage/')) {
                $oldDiskPath = str_replace('storage/', '', $captive->picture);
                Storage::disk('public')->delete($oldDiskPath);
            }
            $path = $request->file('picture')->store('uploads/captives', 'public');
            $data['picture'] = "storage/{$path}";
        } else {
            // If no new file: only accept string picture; otherwise keep existing value
            if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                unset($data['picture']);
            }
        }

        $captive->update($data);

        // reload with consistent resource select
        $captive->load([
            'resource' => function ($qr) {
                $qr->select('id', 'name', 'code_number', 'phone_number');
            }
        ]);

        return new CaptiveResource($captive);
    }

    /**
     * DELETE /captives/{captive}
     */
    public function destroy(Captive $captive)
    {
        // Optional: remove stored file from disk
        if ($captive->picture && str_starts_with($captive->picture, 'storage/')) {
            $oldDiskPath = str_replace('storage/', '', $captive->picture);
            Storage::disk('public')->delete($oldDiskPath);
        }

        $captive->delete();

        return response()->noContent();
    }
}
