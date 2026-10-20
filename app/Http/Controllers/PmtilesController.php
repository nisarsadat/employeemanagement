<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PmtilesController extends Controller
{
    /**
     * Byte-range PMTiles response that behaves the same on Herd (nginx),
     * Ubuntu nginx, Apache/XAMPP, and php-fpm. The archive may live in any
     * of the known folders and is matched without case sensitivity.
     */
    public function serve(Request $request, string $filename)
    {
        $headers = $this->headers();

        if ($request->isMethod('OPTIONS')) {
            return response('', 204, $headers);
        }

        $path = $this->findArchive($filename);
        if ($path === null) {
            abort(404, 'PMTiles file not found');
        }

        @ini_set('zlib.output_compression', '0');
        @ini_set('output_buffering', '0');
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        $response = new BinaryFileResponse($path, 200, $headers, true, null, false, false);
        $response->setAutoEtag(false);
        $response->setAutoLastModified(false);
        $response->headers->remove('Content-Encoding');

        return $response;
    }

    private function headers(): array
    {
        return [
            'Content-Type' => 'application/vnd.pmtiles',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=86400, no-transform',
            'X-Accel-Buffering' => 'no',
            'X-Content-Type-Options' => 'nosniff',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
            'Access-Control-Allow-Headers' => 'Range, Origin, Accept, Content-Type',
            'Access-Control-Expose-Headers' => 'Accept-Ranges, Content-Length, Content-Range, Content-Type',
        ];
    }

    private function findArchive(string $filename): ?string
    {
        $safe = basename(rawurldecode($filename));
        if (! preg_match('/^[A-Za-z0-9._-]+\.pmtiles$/', $safe)) {
            return null;
        }

        $directories = [
            public_path('pmtiles'),
            public_path('pmtiles-range'),
            storage_path('app/pmtiles'),
            storage_path('app/public/pmtiles'),
        ];

        foreach ($directories as $directory) {
            if (! is_dir($directory)) {
                continue;
            }

            $exact = $directory.DIRECTORY_SEPARATOR.$safe;
            if (is_file($exact)) {
                return $exact;
            }

            $entries = @scandir($directory);
            if ($entries === false) {
                continue;
            }

            foreach ($entries as $entry) {
                if (strcasecmp($entry, $safe) === 0 && is_file($directory.DIRECTORY_SEPARATOR.$entry)) {
                    return $directory.DIRECTORY_SEPARATOR.$entry;
                }
            }
        }

        return null;
    }
}
