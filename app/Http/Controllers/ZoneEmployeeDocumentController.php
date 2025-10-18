<?php

namespace App\Http\Controllers\Api;

use App\Models\ZoneEmployee;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class ZoneEmployeeDocumentController extends Controller
{
    public function downloadAll(ZoneEmployee $zoneEmployee)
    {
        $zoneEmployee->load('documents');
        if ($zoneEmployee->documents->isEmpty()) {
            return response()->json(['message' => 'No documents to download'], 404);
        }

        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) mkdir($tmpDir, 0775, true);

        $base = $zoneEmployee->code_number ?: ($zoneEmployee->name ?: 'zone_employee');
        $safe = preg_replace('/[^A-Za-z0-9_\-]+/', '_', mb_strtolower(trim($base)));
        $zipPath = $tmpDir . DIRECTORY_SEPARATOR . $safe . '_' . Str::random(6) . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json(['message' => 'Could not create zip'], 500);
        }

        $added = 0;
        foreach ($zoneEmployee->documents as $doc) {
            $fullPath = Storage::disk('public')->path($doc->file_path);
            if (!is_file($fullPath)) continue;
            $nameInZip = $doc->file_name ?? basename($doc->file_path);
            $zip->addFile($fullPath, $safe . '/' . $nameInZip);
            $added++;
        }

        if ($added === 0) {
            $zip->close(); @unlink($zipPath);
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
