<?php

namespace App\Http\Controllers;

use App\Models\OfficeEmployee;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class OfficeEmployeeDocumentController extends Controller
{
    /**
     * Download all documents of an OfficeEmployee as a ZIP file.
     */
    public function downloadAll(OfficeEmployee $officeEmployee)
    {
        $officeEmployee->load('documents');

        if ($officeEmployee->documents->isEmpty()) {
            return response()->json(['message' => 'No documents to download'], 404);
        }

        // Ensure tmp dir exists
        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }

        // Make a safe base name using code_number if present, else name, else 'office_employee'
        $base = $officeEmployee->code_number
            ?: ($officeEmployee->name ?: 'office_employee');
        $safe = preg_replace('/[^A-Za-z0-9_\-]+/', '_', mb_strtolower(trim($base)));
        $zipPath = $tmpDir . DIRECTORY_SEPARATOR . $safe . '_' . Str::random(6) . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json(['message' => 'Could not create zip'], 500);
        }

        $added = 0;
        foreach ($officeEmployee->documents as $doc) {
            $relativePath = $doc->file_path;
            $fullPath     = Storage::disk('public')->path($relativePath);

            if (!is_file($fullPath)) {
                continue; // skip missing files
            }

            $nameInZip = $doc->file_name ?? basename($relativePath);
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
