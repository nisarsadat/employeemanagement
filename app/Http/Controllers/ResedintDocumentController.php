<?php

namespace App\Http\Controllers\Api;

use ZipArchive;
use App\Models\Resedint;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\ResedintDocumentResource;

class ResedintDocumentController extends Controller
{
    public function index(Resedint $resedint)
    {
        return ResedintDocumentResource::collection($resedint->documents()->latest()->get());
    }

    public function store(Request $request, Resedint $resedint)
    {
        $request->validate([
            'files.*' => ['required','file','max:10240'], // 10MB each
        ]);

        $saved = [];
        foreach ($request->file('files', []) as $file) {
            $path = $file->store("resedints/{$resedint->id}", 'public');
            $doc = $resedint->documents()->create([
                'file_name'  => $file->getClientOriginalName(),
                'file_path'  => $path,
                'mime_type'  => $file->getClientMimeType(),
                'size_bytes' => $file->getSize(),
            ]);
            $saved[] = $doc;
        }

        return ResedintDocumentResource::collection(collect($saved));
    }

    public function destroy(Resedint $resedint, $documentId)
    {
        $doc = $resedint->documents()->whereKey($documentId)->firstOrFail();
        Storage::disk('public')->delete($doc->file_path);
        $doc->delete();

        return response()->noContent();
    }

    public function downloadAll(Resedint $resedint)
    {
        $resedint->load('documents');
        if ($resedint->documents->isEmpty()) {
            return response()->json(['message' => 'No documents to download'], 404);
        }

        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }

        $base = trim($resedint->name ?: 'resedint_documents');
        $safe = preg_replace('/[^A-Za-z0-9_\-]+/', '_', mb_strtolower($base));
        $zipPath = $tmpDir . DIRECTORY_SEPARATOR . $safe . '_' . substr(sha1(uniqid('', true)), 0, 8) . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json(['message' => 'Could not create zip'], 500);
        }

        $added = 0;
        foreach ($resedint->documents as $doc) {
            $fullPath = Storage::disk('public')->path($doc->file_path);
            if (!is_file($fullPath)) continue;

            $nameInZip = $doc->file_name ?? basename($doc->file_path);
            $zip->addFile($fullPath, $safe . '/' . $nameInZip);
            $added++;
        }

        if ($added === 0) {
            $zip->close();
            @unlink($zipPath);
            return response()->json(['message' => 'No valid files found to include'], 404);
        }

        if ($zip->close() === false) {
            @unlink($zipPath);
            return response()->json(['message' => 'Failed to finalize zip'], 500);
        }

        while (ob_get_level() > 0) { ob_end_clean(); }

        return response()->download($zipPath, $safe . '_documents.zip')->deleteFileAfterSend(true);
    }
}
