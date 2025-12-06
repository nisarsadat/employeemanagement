<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\fifthyFormats\Captive;   // ✅ IMPORTANT: correct namespace
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class CaptiveDocumentController extends Controller
{
    /**
     * GET /captives/{captive}/documents/zip
     */
    public function downloadAll(Captive $captive)
    {
        $captive->load('documents');

        if ($captive->documents->isEmpty()) {
            abort(404, 'هیچ سندی برای این اسیر ثبت نشده است.');
        }

        $zipFileName = 'captive_'.$captive->id.'_documents.zip';

        $tempDir = storage_path('app/temp_zips');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zipPath = $tempDir.DIRECTORY_SEPARATOR.$zipFileName;

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'نتوانستم فایل زیپ را بسازم.');
        }

        foreach ($captive->documents as $doc) {
            $absolutePath = Storage::disk('public')->path($doc->file_path);

            if (is_file($absolutePath)) {
                $nameInZip = $doc->file_name ?: basename($absolutePath);
                $zip->addFile($absolutePath, $nameInZip);
            }
        }

        $zip->close();

        if (! is_file($zipPath)) {
            abort(500, 'فایل زیپ ساخته نشد.');
        }

        if (filesize($zipPath) === 0) {
            abort(500, 'فایل زیپ خالی است (هیچ سندی اضافه نشد).');
        }

        // 🔥 clear any stray output (BOM, whitespace, debug) before sending binary
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        return response()->download($zipPath, $zipFileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

}
