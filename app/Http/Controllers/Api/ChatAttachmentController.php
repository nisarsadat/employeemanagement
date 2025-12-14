<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MessageAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChatAttachmentController extends Controller
{
    public function show(Request $request, MessageAttachment $attachment)
    {
        $user = $request->user();

        // ✅ authorize: user must be participant of the conversation
        $attachment->loadMissing('message.conversation.participants:id');

        $conversation = $attachment->message?->conversation;
        abort_unless($conversation, 404);

        $isParticipant = $conversation->participants->contains('id', $user->id);
        abort_unless($isParticipant, 403);

        $disk = Storage::disk('public');
        abort_unless($attachment->path && $disk->exists($attachment->path), 404);

        $absPath = $disk->path($attachment->path);
        $size    = filesize($absPath);
        $mime    = $attachment->mime ?: (mime_content_type($absPath) ?: 'application/octet-stream');

        $headers = [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . addslashes($attachment->name ?: 'file') . '"',
            'Accept-Ranges'       => 'bytes',
        ];

        // ✅ Range support (fixes audio/video duration + seeking)
        $range = $request->header('Range');
        if (!$range) {
            return response()->file($absPath, $headers);
        }

        if (!preg_match('/bytes=(\d+)-(\d*)/i', $range, $m)) {
            return response()->file($absPath, $headers);
        }

        $start = (int) $m[1];
        $end   = ($m[2] !== '') ? (int) $m[2] : ($size - 1);
        $end   = min($end, $size - 1);

        if ($start > $end) {
            return response('', 416, ['Content-Range' => "bytes */{$size}"]);
        }

        $length = $end - $start + 1;

        return response()->stream(function () use ($absPath, $start, $length) {
            $fh = fopen($absPath, 'rb');
            fseek($fh, $start);

            $remaining = $length;
            while ($remaining > 0 && !feof($fh)) {
                $chunk = fread($fh, min(8192, $remaining));
                $remaining -= strlen($chunk);
                echo $chunk;
                flush();
            }
            fclose($fh);
        }, 206, array_merge($headers, [
            'Content-Length' => $length,
            'Content-Range'  => "bytes {$start}-{$end}/{$size}",
        ]));
    }
}
