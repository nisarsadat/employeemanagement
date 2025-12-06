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
    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 15);
        $search  = $request->get('search');
        $rid     = $request->get('resource_id');

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
                },
                'documents', // ✅ eager-load docs
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

    public function show(Captive $captive)
    {
        $captive->load([
            'resource' => function ($qr) {
                $qr->select('id', 'name', 'code_number', 'phone_number');
            },
            'documents', // ✅
        ]);

        return new CaptiveResource($captive);
    }

    public function store(StoreCaptiveRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('picture')) {
            $path = $request->file('picture')->store('uploads/captives', 'public');
            $data['picture'] = "storage/{$path}";
        } else {
            if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                unset($data['picture']);
            }
        }

        $captive = Captive::create($data);

        // ✅ save uploaded documents[]
        if ($request->hasFile('documents')) {
            foreach ((array) $request->file('documents') as $file) {
                if (!$file || !$file->isValid()) {
                    continue;
                }

                $docPath = $file->store('uploads/captive-documents', 'public');

                $captive->documents()->create([
                    'file_name'  => $file->getClientOriginalName(),
                    'file_path'  => $docPath,
                    'mime_type'  => $file->getClientMimeType(),
                    'size_bytes' => $file->getSize(),
                ]);
            }
        }

        $captive->load([
            'resource' => function ($qr) {
                $qr->select('id', 'name', 'code_number', 'phone_number');
            },
            'documents',
        ]);

        return (new CaptiveResource($captive))
            ->additional(['message' => 'Created'])
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateCaptiveRequest $request, Captive $captive)
    {
        $data = $request->validated();

        if ($request->hasFile('picture')) {
            if ($captive->picture && str_starts_with($captive->picture, 'storage/')) {
                $oldDiskPath = str_replace('storage/', '', $captive->picture);
                Storage::disk('public')->delete($oldDiskPath);
            }
            $path = $request->file('picture')->store('uploads/captives', 'public');
            $data['picture'] = "storage/{$path}";
        } else {
            if (array_key_exists('picture', $data) && !is_string($data['picture'])) {
                unset($data['picture']);
            }
        }

        $captive->update($data);

        // ✅ delete selected old documents if front sends removed_document_ids[]
        $removed = (array) $request->input('removed_document_ids', []);
        if (!empty($removed)) {
            $docs = $captive->documents()->whereIn('id', $removed)->get();
            foreach ($docs as $doc) {
                if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }
                $doc->delete();
            }
        }

        // ✅ add new documents[]
        if ($request->hasFile('documents')) {
            foreach ((array) $request->file('documents') as $file) {
                if (!$file || !$file->isValid()) {
                    continue;
                }

                $docPath = $file->store('uploads/captive-documents', 'public');

                $captive->documents()->create([
                    'file_name'  => $file->getClientOriginalName(),
                    'file_path'  => $docPath,
                    'mime_type'  => $file->getClientMimeType(),
                    'size_bytes' => $file->getSize(),
                ]);
            }
        }

        $captive->load([
            'resource' => function ($qr) {
                $qr->select('id', 'name', 'code_number', 'phone_number');
            },
            'documents',
        ]);

        return new CaptiveResource($captive);
    }

    public function destroy(Captive $captive)
    {
        // delete picture file if stored locally
        if ($captive->picture && str_starts_with($captive->picture, 'storage/')) {
            $oldDiskPath = str_replace('storage/', '', $captive->picture);
            Storage::disk('public')->delete($oldDiskPath);
        }

        // docs files are removed by Captive::booted() + rows by FK cascade
        $captive->delete();

        return response()->noContent();
    }
}
    