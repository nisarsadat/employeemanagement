<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controllers (import them, don't declare a namespace here)
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\ResedintController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\ZoneEmployeeController;
use App\Http\Controllers\Api\OfficeEmployeeController;
use App\Http\Controllers\InformationController;
use App\Http\Controllers\InformationDocumentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventDocumentController;
use App\Http\Controllers\CaptiveDeliveryController;
use App\Http\Controllers\CaptiveDeliveryDocumentController;




Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// People
Route::apiResource('resedints', ResedintController::class);
Route::apiResource('zone-employees', ZoneEmployeeController::class);
Route::apiResource('office-employees', OfficeEmployeeController::class);
Route::apiResource('users', UserController::class);

// Database
Route::apiResource('resources', ResourceController::class);
Route::apiResource('informations', InformationController::class);

// Documents (nested)
Route::get('informations/{information}/documents', [InformationDocumentController::class, 'index']);
Route::post('informations/{information}/documents', [InformationDocumentController::class, 'store']);
Route::delete('informations/{information}/documents/{document}', [InformationDocumentController::class, 'destroy']);
Route::get(
    'informations/{information}/documents/zip',
    [InformationDocumentController::class, 'downloadAll']
);
Route::apiResource('events', EventController::class);
// Documents Event routes
Route::get('/events/{event}/documents/zip', [EventDocumentController::class, 'downloadAll']);

// Captive Deliveries CRUD
Route::apiResource('captive-deliveries', CaptiveDeliveryController::class);
// Captive Delivery Documents (nested)
Route::get('captive-deliveries/{captiveDelivery}/documents', [CaptiveDeliveryDocumentController::class, 'index']);
Route::post('captive-deliveries/{captiveDelivery}/documents', [CaptiveDeliveryDocumentController::class, 'store']);
Route::delete('captive-deliveries/{captiveDelivery}/documents/{document}', [CaptiveDeliveryDocumentController::class, 'destroy']);
Route::get('captive-deliveries/{captiveDelivery}/documents/zip', [CaptiveDeliveryDocumentController::class, 'downloadAll']);

// Ping
Route::get('/ping', fn() => ['pong' => true]);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
