<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OutgoingIncomingResource;
use App\Models\OutgoingIncoming;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class OutgoingIncomingController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->get('perPage', 100000));
        $search  = trim((string) $request->get('search', ''));

        $query = OutgoingIncoming::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhere('marja', 'like', "%{$search}%")
                  ->orWhere('who_sign', 'like', "%{$search}%");
            });
        }

        $query->latest('id');

        // ✅ IMPORTANT: load docs
        $items = $query->with('sundryDocuments')->paginate($perPage);

        return OutgoingIncomingResource::collection($items);
    }

    public function store(Request $request)
    {
        // IMPORTANT: because you are sending strings for checkboxes sometimes:
        // We'll cast them safely to boolean if needed.
        $data = $request->all();

        $item = OutgoingIncoming::create($data);

        // Upload documents
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store('outgoing_incomings', 'public');

                $item->sundryDocuments()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        return new OutgoingIncomingResource($item->load('sundryDocuments'));
    }

    public function show($id)
    {
        $item = OutgoingIncoming::with('sundryDocuments')->findOrFail($id);
        return new OutgoingIncomingResource($item);
    }

    public function update(Request $request, $id)
    {
        $item = OutgoingIncoming::with('sundryDocuments')->findOrFail($id);

        $data = $request->all();
        $item->update($data);

        // remove docs
        $removed = $request->input('removed_document_ids', []);
        if (is_array($removed) && count($removed)) {
            $docs = $item->sundryDocuments()->whereIn('id', $removed)->get();
            foreach ($docs as $doc) {
                if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                    Storage::disk('public')->delete($doc->file_path);
                }
                $doc->delete();
            }
        }

        // add new docs
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $path = $file->store('outgoing_incomings', 'public');

                $item->sundryDocuments()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        return new OutgoingIncomingResource($item->load('sundryDocuments'));
    }

    public function destroy($id)
    {
        $item = OutgoingIncoming::with('sundryDocuments')->findOrFail($id);

        foreach ($item->sundryDocuments as $doc) {
            if ($doc->file_path && Storage::disk('public')->exists($doc->file_path)) {
                Storage::disk('public')->delete($doc->file_path);
            }
            $doc->delete();
        }

        $item->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    // ✅ ZIP DOWNLOAD
    public function downloadDocumentsZip($id)
    {
        $item = OutgoingIncoming::with('sundryDocuments')->findOrFail($id);

        if ($item->sundryDocuments->isEmpty()) {
            return response()->json(['message' => 'No documents available'], 404);
        }

        $zipFileName = "outgoing-incoming-{$item->id}-documents.zip";
        $tempDir = storage_path('app/temp');

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $zipPath = $tempDir . DIRECTORY_SEPARATOR . $zipFileName;

        // delete old if exists
        if (file_exists($zipPath)) {
            @unlink($zipPath);
        }

        $zip = new \ZipArchive();

        $open = $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($open !== true) {
            return response()->json([
                'message' => 'Could not create zip file',
                'zip_open_code' => $open,
            ], 500);
        }

        $added = 0;

        foreach ($item->sundryDocuments as $doc) {
            $fullPath = storage_path('app/public/' . $doc->file_path);

            if (!file_exists($fullPath)) {
                continue;
            }

            // ✅ Windows-safe filename (avoid weird chars)
            $name = $doc->file_name ?: basename($fullPath);
            $name = preg_replace('/[^\w\-. ]+/u', '_', $name);

            $zip->addFile($fullPath, $name);
            $added++;
        }

        $zip->close();

        clearstatcache(true, $zipPath);

        if ($added === 0 || !file_exists($zipPath) || filesize($zipPath) < 22) {
            return response()->json(['message' => 'Zip created but empty/invalid'], 500);
        }

        // ✅ prevent ANY extra output corrupting the zip
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        return response()->download($zipPath, $zipFileName, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="'.$zipFileName.'"',
        ])->deleteFileAfterSend(true);
    }


}
