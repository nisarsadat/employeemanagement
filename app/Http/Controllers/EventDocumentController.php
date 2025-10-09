<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class EventDocumentController extends Controller
{
    /**
     * Download all documents related to a specific event as a ZIP file.
     */
    public function downloadAll(Event $event)
    {
        // Make sure the relationship exists in Event model:
        // public function documents() { return $this->hasMany(EventDocument::class); }
        $event->load('documents');

        if ($event->documents->isEmpty()) {
            return response()->json(['message' => 'No documents to download'], 404);
        }

        // Ensure tmp dir exists
        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }

        // Safe base name from event ID or title (if you have one)
        $base = trim($event->event_type ?: 'event_documents');
        $safe = preg_replace('/[^A-Za-z0-9_\-]+/', '_', mb_strtolower($base));
        $zipPath = $tmpDir . DIRECTORY_SEPARATOR . $safe . '_' . Str::random(6) . '.zip';

        // Create ZIP
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return response()->json(['message' => 'Could not create zip'], 500);
        }

        $added = 0;
        foreach ($event->documents as $doc) {
            // If you stored with $file->store("events/{$event->id}", "public")
            $relativePath = $doc->file_path; // make sure your model uses 'file_path'
            $fullPath     = Storage::disk('public')->path($relativePath);

            if (!is_file($fullPath)) {
                // skip missing files instead of corrupting the zip
                continue;
            }

            // Pick a nice name inside the zip
            $nameInZip = $doc->file_name ?? basename($relativePath);

            // Optionally nest inside a folder named after the event
            $zip->addFile($fullPath, $safe . '/' . $nameInZip);
            $added++;
        }

        // If nothing got added, abort
        if ($added === 0) {
            $zip->close();
            @unlink($zipPath);
            return response()->json(['message' => 'No valid files found to include'], 404);
        }

        // Finalize the zip
        if ($zip->close() === false) {
            @unlink($zipPath);
            return response()->json(['message' => 'Failed to finalize zip'], 500);
        }

        // Avoid BOM/whitespace corrupting the archive
        while (ob_get_level() > 0) { ob_end_clean(); }

        // Stream the file and delete after send
        return response()->download($zipPath, $safe . '_documents.zip')->deleteFileAfterSend(true);
    }
}
