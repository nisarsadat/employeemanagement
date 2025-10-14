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
// FIFTHY FORMATS controllers
use App\Http\Controllers\fifthyFormats\FasadController;
use App\Http\Controllers\fifthyFormats\SaeenFasadController;
use App\Http\Controllers\fifthyFormats\HeistController;
use App\Http\Controllers\fifthyFormats\CaptiveController;
use App\Http\Controllers\fifthyFormats\MulkanoController;
use App\Http\Controllers\fifthyFormats\EntryExitController;
use App\Http\Controllers\fifthyFormats\SarafyanoController;
use App\Http\Controllers\fifthyFormats\ClubsController;
use App\Http\Controllers\fifthyFormats\MahajeratController;
use App\Http\Controllers\fifthyFormats\SoldierController;
// FIFTHY FORMAT ONE (new set)
use App\Http\Controllers\fifthyformatone\MawadController;
use App\Http\Controllers\fifthyformatone\LostMujremeenController;
use App\Http\Controllers\fifthyformatone\OldSoldiersHomesController;
use App\Http\Controllers\fifthyformatone\RobbersController;
use App\Http\Controllers\fifthyformatone\NonResponsablePeopleController;
use App\Http\Controllers\fifthyformatone\MashkokHomesController;
use App\Http\Controllers\fifthyformatone\DamagePlacesController;
use App\Http\Controllers\fifthyformatone\DamageRasanPlacesController;
use App\Http\Controllers\fifthyformatone\MashkokShopsController;
use App\Http\Controllers\fifthyformatone\LuchakanoController;
use App\Http\Controllers\fifthyformatsthree\BadmashanoController;
use App\Http\Controllers\fifthyformatsthree\EmaratOfficeController;
use App\Http\Controllers\fifthyformatsthree\HostelController;
use App\Http\Controllers\fifthyformatsthree\HotelController;
use App\Http\Controllers\fifthyformatsthree\MadrasaController;
use App\Http\Controllers\fifthyformatsthree\MashkokPeopleController;
use App\Http\Controllers\fifthyformatsthree\MujahedinoController;
use App\Http\Controllers\fifthyformatsthree\OldJahadiController;
use App\Http\Controllers\fifthyformatsthree\SarayanoController;
use App\Http\Controllers\fifthyformatsthree\TujaranController;
// ---------------------------------------------------------
// FIFTHY FORMATS TWO (Group Two)
// ---------------------------------------------------------
use App\Http\Controllers\fifthyformatstow\SchoolController;
use App\Http\Controllers\fifthyformatstow\HadoBusStopController;
use App\Http\Controllers\fifthyformatstow\TransportCompanyController;
use App\Http\Controllers\fifthyformatstow\CarSalesCompanyController;
use App\Http\Controllers\fifthyformatstow\ParkingController;
use App\Http\Controllers\fifthyformatstow\BankController;
use App\Http\Controllers\fifthyformatstow\ApartmentController;
use App\Http\Controllers\fifthyformatstow\RahnomaMamlatController;
use App\Http\Controllers\fifthyformatstow\HosppitalController;
use App\Http\Controllers\fifthyformatstow\WorkShopController;

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
// 50 formats
// Fasad
Route::apiResource('fasads', FasadController::class);
// SaeenFasad
Route::apiResource('saeen-fasads', SaeenFasadController::class);
// Heist
Route::apiResource('heists', HeistController::class);
// Captive
Route::apiResource('captives', CaptiveController::class);
// Mulkano
Route::apiResource('mulkanos', MulkanoController::class);
// EntryExit
Route::apiResource('entry-exits', EntryExitController::class);
// Sarafyano
Route::apiResource('sarafyanos', SarafyanoController::class);
// Clubs
Route::apiResource('clubs', ClubsController::class);
// Mahajerat
Route::apiResource('mahajerats', MahajeratController::class);
// Soldier
Route::apiResource('soldiers', SoldierController::class);
/*
|--------------------------------------------------------------------------
| 50 FORMAT ONE (NEW CONTROLLERS)
|--------------------------------------------------------------------------
*/
// Mawads
Route::apiResource('mawads', MawadController::class);
Route::post('mawads/bulk-delete', [MawadController::class, 'bulkDelete']);

// Lost Mujremeens
Route::apiResource('lost-mujremeens', LostMujremeenController::class);
Route::post('lost-mujremeens/bulk-delete', [LostMujremeenController::class, 'bulkDelete']);

// Old Soldiers Homes
Route::apiResource('old-soldiers-homes', OldSoldiersHomesController::class);
Route::post('old-soldiers-homes/bulk-delete', [OldSoldiersHomesController::class, 'bulkDelete']);

// Robbers
Route::apiResource('robbers', RobbersController::class);
Route::post('robbers/bulk-delete', [RobbersController::class, 'bulkDelete']);

// Non Responsable People
Route::apiResource('non-responsable-people', NonResponsablePeopleController::class);
Route::post('non-responsable-people/bulk-delete', [NonResponsablePeopleController::class, 'bulkDelete']);

// Mashkok Homes
Route::apiResource('mashkok-homes', MashkokHomesController::class);
Route::post('mashkok-homes/bulk-delete', [MashkokHomesController::class, 'bulkDelete']);

// Damage Places
Route::apiResource('damage-places', DamagePlacesController::class);
Route::post('damage-places/bulk-delete', [DamagePlacesController::class, 'bulkDelete']);

// Damage Rasan Places
Route::apiResource('damage-rasan-places', DamageRasanPlacesController::class);
Route::post('damage-rasan-places/bulk-delete', [DamageRasanPlacesController::class, 'bulkDelete']);

// Mashkok Shops
Route::apiResource('mashkok-shops', MashkokShopsController::class);
Route::post('mashkok-shops/bulk-delete', [MashkokShopsController::class, 'bulkDelete']);

// Luchakanos
Route::apiResource('luchakanos', LuchakanoController::class);
Route::post('luchakanos/bulk-delete', [LuchakanoController::class, 'bulkDelete']);

//  Schools
Route::apiResource('schools', SchoolController::class);
Route::delete('schools-bulk', [SchoolController::class, 'bulkDelete']);

//  Hado Bus Stops
Route::apiResource('hado-bus-stops', HadoBusStopController::class);
Route::delete('hado-bus-stops-bulk', [HadoBusStopController::class, 'bulkDelete']);

//  Transport Companies
Route::apiResource('transport-companies', TransportCompanyController::class);
Route::delete('transport-companies-bulk', [TransportCompanyController::class, 'bulkDelete']);

//  Car Sales Companies
Route::apiResource('car-sales-companies', CarSalesCompanyController::class);
Route::delete('car-sales-companies-bulk', [CarSalesCompanyController::class, 'bulkDelete']);

//  Parkings
Route::apiResource('parkings', ParkingController::class);
Route::delete('parkings-bulk', [ParkingController::class, 'bulkDelete']);

// Banks
Route::apiResource('banks', BankController::class);
Route::delete('banks-bulk', [BankController::class, 'bulkDelete']);

// Apartments
Route::apiResource('apartments', ApartmentController::class);
Route::delete('apartments-bulk', [ApartmentController::class, 'bulkDelete']);

// Rahnoma Mamlat
Route::apiResource('rahnoma-mamlats', RahnomaMamlatController::class);
Route::delete('rahnoma-mamlats-bulk', [RahnomaMamlatController::class, 'bulkDelete']);

// Hosppitals
Route::apiResource('hosppitals', HosppitalController::class);
Route::delete('hosppitals-bulk', [HosppitalController::class, 'bulkDelete']);

// WorkShops
Route::apiResource('work-shops', WorkShopController::class);
Route::delete('work-shops-bulk', [WorkShopController::class, 'bulkDelete']);

// Badamashano
Route::apiResource('badmashanos', BadmashanoController::class);
Route::delete('badmashanos', [BadmashanoController::class, 'bulkDelete']);

//Sarayano
Route::apiResource('sarayanos', SarayanoController::class);
Route::delete('sarayanos', [SarayanoController::class, 'bulkDelete']);

//Hotel
Route::apiResource('hotels', HotelController::class);
Route::delete('hotels', [HotelController::class, 'bulkDelete']);

//Hostel
Route::apiResource('hostels', HostelController::class);
Route::delete('hostels', [HostelController::class, 'bulkDelete']);

//Tujaran
Route::apiResource('tujarans', TujaranController::class);
Route::delete('tujarans', [TujaranController::class, 'bulkDelete']);

//Mujahedino
Route::apiResource('mujahedinos', MujahedinoController::class);
Route::delete('mujahedinos', [MujahedinoController::class, 'bulkDelete']);

//Emarat Offices 
Route::apiResource('emarat-offices', EmaratOfficeController::class);
Route::delete('emarat-offices', [EmaratOfficeController::class, 'bulkDelete']);

//Old Jahadi 
Route::apiResource('old-jahadis', OldJahadiController::class);
Route::delete('old-jahadis', [OldJahadiController::class, 'bulkDelete']);

//Mashkok People 
Route::apiResource('mashkok-people', MashkokPeopleController::class)->parameters([
    'mashkok-people' => 'mashkokPeople', 
]);Route::delete('mashkok-people', [MashkokPeopleController::class, 'bulkDelete']);

//Madrasa
Route::apiResource('madrasas', MadrasaController::class);
Route::delete('madrasas', [MadrasaController::class, 'bulkDelete']);


// Ping
Route::get('/ping', fn() => ['pong' => true]);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
