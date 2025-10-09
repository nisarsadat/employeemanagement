<?php

namespace App\Http\Controllers;

use App\Http\Resources\CaptiveDeliveryDocumentResource;
use App\Models\CaptiveDelivery;
use App\Models\CaptiveDeliveryDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class CaptiveDeliveryDocumentController extends Controller
{
    public function index(CaptiveDelivery $captiveDelivery)
    {
        return response()->json([
            'data' => CaptiveDeliveryDocumentResource::collection($captiveDelivery->documents()->orderByDesc('id')->get())
        ]);
    }

    public function store(Request $request, CaptiveDelivery $captiveDelivery)
    {
        $request->validate([
            'files'   => ['required','array'],
            'files.*' => ['file','max:20480'],
        ]);

        $out = [];
        foreach ($request->file('files', []) as $file) {
            $path = $file->store("captive_deliveries/{$captiveDelivery->id}", 'public');
            $doc = CaptiveDeliveryDocument::create([
                'captive_delivery_id' => $captiveDelivery->id,
                'file_name'  => $file->getClientOriginalName(),
                'file_path'  => $path,
                'mime_type'  => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
            ]);
            $out[] = $doc;
        }

        return response()->json([
            'data' => CaptiveDeliveryDocumentResource::collection(collect($out))
        ], 201);
    }

    public function destroy(CaptiveDelivery $captiveDelivery, CaptiveDeliveryDocument $document)
    {
        // ensure doc belongs to this parent
        if ($document->captive_delivery_id !== $captiveDelivery->id) {
            abort(404);
        }

        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return response()->json(['message' => 'Removed']);
    }

    public function downloadAll(CaptiveDelivery $captiveDelivery)
{
    $docs = $captiveDelivery->documents()->get();

    if ($docs->isEmpty()) {
        return response()->json(['message' => 'No documents to download'], 404);
    }

    $zipFileName = 'captive_'.$captiveDelivery->id.'_documents.zip';
    $tmpPath = storage_path('app/tmp/'.$zipFileName);

    if (!is_dir(dirname($tmpPath))) {
        @mkdir(dirname($tmpPath), 0777, true);
    }

    $zip = new \ZipArchive();
    if ($zip->open($tmpPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
        return response()->json(['message' => 'Failed to create zip'], 500);
    }

    foreach ($docs as $doc) {
        $abs = \Illuminate\Support\Facades\Storage::disk('public')->path($doc->file_path);
        if (is_file($abs)) {
            // use the original name inside archive; fallback to basename
            $zip->addFile($abs, $doc->file_name ?: basename($abs));
        }
    }

    $zip->close();

    // ensure file actually exists and is non-zero
    clearstatcache(true, $tmpPath);
    if (!is_file($tmpPath) || filesize($tmpPath) === 0) {
        @unlink($tmpPath);
        return response()->json(['message' => 'ZIP creation failed'], 500);
    }

    // Important: correct headers so the client recognizes a zip
    $headers = [
        'Content-Type'        => 'application/zip',
        'Content-Disposition' => 'attachment; filename="'.$zipFileName.'"',
        'Content-Length'      => filesize($tmpPath),
        'X-Content-Type-Options' => 'nosniff',
    ];

    // If PHP output buffering is on, clear it to avoid corrupting the file
    if (function_exists('ob_get_length') && ob_get_length()) {
        @ob_end_clean();
    }

    return response()->download($tmpPath, $zipFileName, $headers)->deleteFileAfterSend(true);
}

}
