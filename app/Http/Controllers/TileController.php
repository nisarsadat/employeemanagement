<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TileController extends Controller
{
    public function serve(Request $request, string $name)
    {
        $path = public_path("pmtiles/{$name}.pmtiles");
        if (!is_file($path)) {
            abort(404);
        }

        $size  = filesize($path);
        $start = 0;
        $end   = $size - 1;

        $status = 200;
        $headers = [
            'Content-Type'  => 'application/octet-stream',
            'Accept-Ranges' => 'bytes',
        ];

        if ($request->headers->has('Range')) {
            if (preg_match('/bytes=(\d*)-(\d*)/', $request->header('Range'), $m)) {
                if ($m[1] !== '') $start = (int)$m[1];
                if ($m[2] !== '') $end   = (int)$m[2];
                if ($end >= $size) $end = $size - 1;
                if ($start > $end || $start >= $size) {
                    return response('', 416, ['Content-Range' => "bytes */{$size}"]);
                }
                $status = 206;
                $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
            }
        }

        $length = $end - $start + 1;
        $headers['Content-Length'] = (string) $length;

        $response = new StreamedResponse(function () use ($path, $start, $length) {
            $fp = fopen($path, 'rb');
            fseek($fp, $start);
            $remaining = $length;
            while ($remaining > 0 && !feof($fp)) {
                $chunk = fread($fp, min(8192, $remaining));
                if ($chunk === false) break;
                echo $chunk;
                $remaining -= strlen($chunk);
                if (ob_get_level()) { @ob_flush(); }
                flush();
            }
            fclose($fp);
        }, $status, $headers);

        // Avoid middleware/compression altering Content-Length
        $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');

        return $response;
    }
}
