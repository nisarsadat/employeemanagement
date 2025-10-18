<?php

namespace App\Http\Controllers\Api;

use App\Models\Resedint;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResedintRequest;
use App\Http\Resources\ResedintResource;

class ResedintController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('can:viewResedint')->only(['index','show']);
    //     $this->middleware('can:createResedint')->only('store');
    //     $this->middleware('can:updateResedint')->only('update');
    //     $this->middleware('can:deleteResedint')->only('destroy');
    // }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 15);
        $search  = $request->input('search');

        $query = Resedint::query()
            ->with(['officeEmployee'])
            ->search($search)
            ->latest();

        $page = $query->paginate($perPage);

        return ResedintResource::collection($page);
    }

    public function store(ResedintRequest $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // picture
            if ($request->hasFile('picture')) {
                $path = $request->file('picture')->store('resedints/pictures', 'public');
                // store path without "storage/" (we’ll use Storage::disk to get url)
                $data['picture'] = $path;
            }

            $resedint = Resedint::create($data);

            // documents (optional)
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store("resedints/{$resedint->id}", 'public');
                    $resedint->documents()->create([
                        'file_name'  => $file->getClientOriginalName(),
                        'file_path'  => $path,
                        'mime_type'  => $file->getClientMimeType(),
                        'size_bytes' => $file->getSize(),
                    ]);
                }
            }

            DB::commit();

            return ResedintResource::make(
                $resedint->load(['documents', 'officeEmployee'])
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Resedint creation failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Resedint $resedint)
    {
        return ResedintResource::make(
            $resedint->load(['documents', 'officeEmployee'])
        );
    }

    public function update(ResedintRequest $request, Resedint $resedint)
    {
        DB::beginTransaction();

        try {
            $data = $request->validated();

            // picture replacement
            if ($request->hasFile('picture')) {
                // delete old if exists
                if ($resedint->picture) {
                    Storage::disk('public')->delete($resedint->picture);
                }
                $data['picture'] = $request->file('picture')->store('resedints/pictures', 'public');
            } else {
                unset($data['picture']); // don't nullify if not sent
            }

            $resedint->update($data);

            // new documents (if any)
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store("resedints/{ $resedint->id }", 'public');
                    $resedint->documents()->create([
                        'file_name'  => $file->getClientOriginalName(),
                        'file_path'  => $path,
                        'mime_type'  => $file->getClientMimeType(),
                        'size_bytes' => $file->getSize(),
                    ]);
                }
            }

            DB::commit();

            return ResedintResource::make(
                $resedint->load(['documents', 'officeEmployee'])
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Resedint update failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Resedint $resedint)
    {
        // delete files from storage
        foreach ($resedint->documents as $doc) {
            Storage::disk('public')->delete($doc->file_path);
        }
        if ($resedint->picture) {
            Storage::disk('public')->delete($resedint->picture);
        }

        // delete DB rows (docs cascade by FK + resedint)
        $resedint->documents()->delete();
        $resedint->delete();

        return response()->noContent();
    }
}
