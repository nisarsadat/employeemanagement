<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MapGlyphController;
use App\Http\Controllers\PmtilesController;

$binaryMiddleware = [
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
];

// Byte-range archives. These run only when the web server did not already
// serve the file from public/ (Herd, nginx, and Apache all do that first).
Route::match(['GET', 'HEAD', 'OPTIONS'], '/pmtiles-range/{filename}', [PmtilesController::class, 'serve'])
    ->where('filename', '[^/]+\.pmtiles')
    ->withoutMiddleware($binaryMiddleware);

Route::match(['GET', 'HEAD', 'OPTIONS'], '/pmtiles/{filename}', [PmtilesController::class, 'serve'])
    ->where('filename', '[^/]+\.pmtiles')
    ->withoutMiddleware($binaryMiddleware);

// Combined font stacks (NotoNaskhArabic-Regular,NotoSans-Regular) are not real
// folders. A single font file is still served directly from public/fonts.
Route::get('/fonts/{fontstack}/{range}.pbf', [MapGlyphController::class, 'show'])
    ->where('fontstack', '[^/]+')
    ->where('range', '\d+-\d+')
    ->withoutMiddleware($binaryMiddleware);


Route::get('/', function () {
    return view('welcome');
});



// your map view
// Route::view('/map-afg', 'map-afg');

// ❌ keep this LAST so it doesn't swallow everything
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
