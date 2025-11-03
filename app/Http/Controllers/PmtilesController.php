<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PmtilesController extends Controller
{
    // Streams a PMTiles file with HTTP byte-range support (dev-safe)
    public function serve(Request $request, string $filename)
    {
        // make sure the file is in public/pmtiles
        $safe = basename($filename); // prevent paths
        $path = public_path('pmtiles/' . $safe);

        if (!is_file($path)) {
            abort(404, 'PMTiles file not found');
        }

        // disable output buffering/chunking
        @ini_set('zlib.output_compression', 'Off');
        while (ob_get_level() > 0) { @ob_end_clean(); }

        $size   = filesize($path);
        $start  = 0;
        $end    = $size - 1;
        $status = 200;

        // parse Range: bytes=start-end
        if ($range = $request->header('Range')) {
            if (preg_match('/bytes=(\d*)-(\d*)/i', $range, $m)) {
                if ($m[1] !== '') $start = (int)$m[1];
                if ($m[2] !== '') $end   = min((int)$m[2], $size - 1);
                if ($start > $end || $start >= $size) {
                    return response('', 416, ['Content-Range' => "bytes */$size"]);
                }
                $status = 206;
            }
        }

        $length = $end - $start + 1;

        $headers = [
            'Content-Type'      => 'application/vnd.pmtiles',
            'Accept-Ranges'     => 'bytes',
            'Content-Encoding'  => 'identity',
            'Cache-Control'     => 'no-transform, public, max-age=0',
            'Content-Length'    => $length,
        ];
        if ($status === 206) {
            $headers['Content-Range'] = "bytes $start-$end/$size";
        }

        return new StreamedResponse(function () use ($path, $start, $end) {
            $fh = fopen($path, 'rb');
            fseek($fh, $start);
            $left = $end - $start + 1;
            $chunk = 8192;
            while ($left > 0 && !feof($fh)) {
                $read = ($left > $chunk) ? $chunk : $left;
                echo fread($fh, $read);
                $left -= $read;
                @ob_flush(); flush();
            }
            fclose($fh);
        }, $status, $headers);
    }
}
