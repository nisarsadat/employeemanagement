<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PmtilesController;


// ✅ PMTiles route (MUST be above the catch-all)
Route::get('/pmtiles-range/{filename}', [PmtilesController::class, 'serve'])
    ->where('filename', '.*');


Route::get('/', function () {
    return view('welcome');
});



// your map view
// Route::view('/map-afg', 'map-afg');

// ❌ keep this LAST so it doesn't swallow everything
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
