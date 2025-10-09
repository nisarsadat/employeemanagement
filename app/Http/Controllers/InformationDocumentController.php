<?php

namespace App\Http\Controllers;

use App\Models\Information;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InformationDocumentController extends Controller
{
    public function downloadAll(Information $information)
    {
        // Defensive: stop any output buffering that could corrupt the zip
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        $docs = $information->documents()->get();

        // Create a temp file for the zip
        $slug = Str::slug($information->name ?: 'documents', '_');
        $zipName = "{$slug}_documents.zip";
        $tmpFile = tempnam(sys_get_temp_dir(), 'zip_');

        $zip = new \ZipArchive();
        $ok = $zip->open($tmpFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($ok !== true) {
            abort(500, 'Cannot create ZIP file.');
        }

        $added = 0;

        foreach ($docs as $doc) {
            // Skip docs with missing path
            if (empty($doc->file_path)) {
                continue;
            }

            // Build absolute path on 'public' disk
            $absolute = Storage::disk('public')->path($doc->file_path);

            // Only add if the file exists and is readable
            if (is_file($absolute) && is_readable($absolute)) {
                // Prefer saved original name; otherwise, use stored basename
                $nameInZip = $doc->file_name ?: basename($absolute);
                // Avoid directory traversal and weird chars
                $nameInZip = ltrim(str_replace(['\\', ':'], ['/', '-'], $nameInZip), '/');

                $zip->addFile($absolute, $nameInZip);
                $added++;
            }
        }

        // Finalize the zip
        $zip->close();

        // If nothing added, clean up and 404
        clearstatcache(true, $tmpFile);
        if ($added === 0 || !filesize($tmpFile)) {
            @unlink($tmpFile);
            abort(404, 'No valid files to download.');
        }

        // Stream the finished file; Laravel sets headers for us
        return response()->download($tmpFile, $zipName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    public function downloadOne(Information $information, int $documentId)
    {
        $doc = $information->documents()->findOrFail($documentId);

        if (empty($doc->file_path)) {
            abort(404, 'File path missing.');
        }

        $absolute = Storage::disk('public')->path($doc->file_path);
        if (!is_file($absolute)) {
            abort(404, 'File not found on disk.');
        }

        $downloadName = $doc->file_name ?: basename($absolute);
        return response()->download($absolute, $downloadName);
    }
}



