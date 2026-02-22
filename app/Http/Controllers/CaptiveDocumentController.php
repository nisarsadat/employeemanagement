<?php

namespace App\Http\Controllers;

use App\Models\fifthyFormats\Captive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class CaptiveDocumentController extends Controller
{
    /**
     * Download all documents related to a specific captive as a ZIP file.
     */
    public function downloadAll(Captive $captive)
    {
        // Make sure relationship exists in Captive model:
        // public function documents() { return $this->hasMany(CaptiveDocument::class); }
        $captive->load('documents');

        if ($captive->documents->isEmpty()) {
            return response()->json(['message' => 'No documents to download'], 404);
        }

        // Ensure tmp directory exists
        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }

        // Safe base name from captive name or fallback
        $base = trim($captive->name ?? 'captive_documents');
        $safe = preg_replace('/[^A-Za-z0-9_\-]+/', '_', mb_strtolower($base));
        $zipPath = $tmpDir . DIRECTORY_SEPARATOR . $safe . '_' . Str::random(6) . '.zip';

        // Create ZIP
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json(['message' => 'Could not create zip'], 500);
        }

        $added = 0;

        foreach ($captive->documents as $doc) {

            $relativePath = $doc->file_path;
            $fullPath     = Storage::disk('public')->path($relativePath);

            if (!is_file($fullPath)) {
                continue; // skip missing files
            }

            $nameInZip = $doc->file_name ?? basename($relativePath);

            // Optional: put inside folder named after captive
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

        // Prevent corrupted zip (important in production)
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        return response()
            ->download($zipPath, $safe . '_documents.zip')
            ->deleteFileAfterSend(true);
    }
}