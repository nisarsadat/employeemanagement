<?php

namespace App\Http\Controllers;

use App\Support\MapboxGlyphs;
use Illuminate\Http\Request;

class MapGlyphController extends Controller
{
    public function show(Request $request, string $fontstack, string $range)
    {
        $fontstack = rawurldecode($fontstack);
        $range = rawurldecode($range);

        if (! preg_match('/^\d+-\d+$/', $range) || str_contains($fontstack, '..')) {
            abort(404);
        }

        $body = MapboxGlyphs::render($fontstack, $range);

        return response($body, 200, [
            'Content-Type' => 'application/x-protobuf',
            'Content-Length' => (string) strlen($body),
            'Cache-Control' => 'public, max-age=86400, no-transform',
            'X-Content-Type-Options' => 'nosniff',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}
