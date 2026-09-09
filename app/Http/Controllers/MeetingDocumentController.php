<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\MeetingDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class MeetingDocumentController extends Controller
{
    public function index(Meeting $meeting)
    {
        $documents =
            $meeting
                ->documents()
                ->latest('id')
                ->get()
                ->map(
                    function ($document) {
                        return [
                            'id' =>
                                $document->id,

                            'meeting_id' =>
                                $document->meeting_id,

                            'file_name' =>
                                $document->file_name,

                            'original_name' =>
                                $document->file_name,

                            'file_path' =>
                                $document->file_path,

                            'mime_type' =>
                                $document->mime_type,

                            'size_bytes' =>
                                $document->size_bytes,

                            'url' =>
                                $document->file_path
                                    ? asset(
                                        'storage/' .
                                        $document
                                            ->file_path
                                    )
                                    : null,
                        ];
                    }
                );

        return response()->json([
            'data' =>
                $documents,
        ]);
    }

    public function store(
        Request $request,
        Meeting $meeting
    ) {
        $request->validate([
            'files' =>
                'required|array|min:1',

            'files.*' =>
                'required|file|max:20480',
        ]);

        $created = [];

        foreach (
            $request->file(
                'files'
            )
            as $file
        ) {
            $path =
                $file->store(
                    "meetings/{$meeting->id}",
                    'public'
                );

            $document =
                $meeting
                    ->documents()
                    ->create([
                        'file_name' =>
                            $file
                                ->getClientOriginalName(),

                        'file_path' =>
                            $path,

                        'mime_type' =>
                            $file
                                ->getClientMimeType(),

                        'size_bytes' =>
                            $file->getSize(),
                    ]);

            $created[] = [
                'id' =>
                    $document->id,

                'meeting_id' =>
                    $document->meeting_id,

                'file_name' =>
                    $document->file_name,

                'original_name' =>
                    $document->file_name,

                'file_path' =>
                    $document->file_path,

                'mime_type' =>
                    $document->mime_type,

                'size_bytes' =>
                    $document->size_bytes,

                'url' =>
                    asset(
                        'storage/' .
                        $document
                            ->file_path
                    ),
            ];
        }

        return response()->json([
            'message' =>
                'Documents uploaded successfully',

            'data' =>
                $created,
        ], 201);
    }

    public function destroy(
        Meeting $meeting,
        MeetingDocument $document
    ) {
        if (
            (int) $document->meeting_id !==
            (int) $meeting->id
        ) {
            abort(404);
        }

        $document->delete();

        return response()->json([
            'message' =>
                'Document deleted successfully',
        ]);
    }

    public function downloadAll(
        Meeting $meeting
    ) {
        $documents =
            $meeting
                ->documents()
                ->get();

        if ($documents->isEmpty()) {
            return response()->json([
                'message' =>
                    'No documents found',
            ], 404);
        }

        $tempDirectory =
            storage_path(
                'app/temp'
            );

        File::ensureDirectoryExists(
            $tempDirectory
        );

        $zipPath =
            $tempDirectory .
            DIRECTORY_SEPARATOR .
            'meeting_' .
            $meeting->id .
            '_documents_' .
            uniqid() .
            '.zip';

        $zip =
            new ZipArchive();

        if (
            $zip->open(
                $zipPath,
                ZipArchive::CREATE |
                ZipArchive::OVERWRITE
            ) !== true
        ) {
            return response()->json([
                'message' =>
                    'Unable to create ZIP file',
            ], 500);
        }

        foreach (
            $documents
            as $document
        ) {
            if (
                !$document->file_path
            ) {
                continue;
            }

            if (
                !Storage::disk(
                    'public'
                )->exists(
                    $document->file_path
                )
            ) {
                continue;
            }

            $absolutePath =
                Storage::disk(
                    'public'
                )->path(
                    $document->file_path
                );

            $fileName =
                $document->file_name
                ?: basename(
                    $document
                        ->file_path
                );

            $zip->addFile(
                $absolutePath,
                $fileName
            );
        }

        $zip->close();

        return response()
            ->download(
                $zipPath,
                'meeting_' .
                $meeting->id .
                '_documents.zip'
            )
            ->deleteFileAfterSend(
                true
            );
    }
}