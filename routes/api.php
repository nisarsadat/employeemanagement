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
use App\Http\Controllers\Api\ResedintDocumentController;
use App\Http\Controllers\Api\ZoneEmployeeDocumentController;
use App\Http\Controllers\InformationController;
use App\Http\Controllers\InformationDocumentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventDocumentController;
use App\Http\Controllers\CaptiveDeliveryController;
use App\Http\Controllers\CaptiveDeliveryDocumentController;

// ✅ FINANCE (NEW)
use App\Http\Controllers\Api\AccountTypeController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\ExpenseController;


// FIFTHY FORMATS controllers
use App\Http\Controllers\fifthyFormats\{
    FasadController, SaeenFasadController, HeistController, CaptiveController, MulkanoController,
    EntryExitController, SarafyanoController, ClubsController, MahajeratController, SoldierController
};

// FIFTHY FORMAT ONE
use App\Http\Controllers\fifthyformatone\{
    MawadController, LostMujremeenController, OldSoldiersHomesController, RobbersController,
    NonResponsablePeopleController, MashkokHomesController, DamagePlacesController,
    DamageRasanPlacesController, MashkokShopsController, LuchakanoController
};

// FIFTHY FORMAT THREE
use App\Http\Controllers\fifthyformatsthree\{
    BadmashanoController, EmaratOfficeController, HostelController, HotelController,
    MadrasaController, MashkokPeopleController, MujahedinoController,
    OldJahadiController, SarayanoController, TujaranController
};

// FIFTHY FORMAT TWO
use App\Http\Controllers\fifthyformatstow\{
    SchoolController, HadoBusStopController, TransportCompanyController, CarSalesCompanyController,
    ParkingController, BankController, ApartmentController, RahnomaMamlatController,
    HosppitalController, WorkShopController
};

// FIFTHY FORMAT FOUR
use App\Http\Controllers\Api\fifthyformatfour\{
    MasjidController, HighwayController, CompanyController, DoneLostPlaceController,
    EconemyProjectController, CriminalCapturePlaceController, PumpController,
    ShoraController, GovernmentPlaceController, SuperMarketController,
    ParkController, ComplexController, DeplomaticPlaceController,
    UniversityController,
    WakilanoController
};
use App\Http\Controllers\Dashboard\FifthyFormatOneDashboardController;
use App\Http\Controllers\Dashboard\InfoEventDashboardController;
use App\Http\Controllers\Dashboard\PeopleDashboardController;
use App\Http\Controllers\Dashboard\FiftyFormatsDashboardController;
use App\Http\Controllers\OfficeEmployeeDocumentController;
use App\Http\Controllers\OilAccountController;
use App\Http\Controllers\IncomeOilController;
use App\Http\Controllers\ExpenseOilController;
use App\Http\Controllers\OilReportController;
use App\Http\Controllers\FinanceReportController;

use App\Http\Controllers\PmtilesController;
use App\Http\Controllers\Dashboard\FiftyFormatsDashboardController as FFD;
use App\Http\Controllers\Dashboard\FifthyFormatsTwoDashboardController ;


// ---------------------- AUTH ----------------------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// ---------------------- BASE MODULES ----------------------
Route::apiResource('resedints', ResedintController::class);
Route::get('resedints/{resedint}/documents', [ResedintDocumentController::class, 'index']);
Route::post('resedints/{resedint}/documents', [ResedintDocumentController::class, 'store']);
Route::delete('resedints/{resedint}/documents/{doc}', [ResedintDocumentController::class, 'destroy']);
Route::get('resedints/{resedint}/documents/zip', [ResedintDocumentController::class, 'downloadAll']);



Route::apiResource('office-employees', OfficeEmployeeController::class);
// ✅ NEW: counts endpoint (active / inactive / total)
Route::get('office-employees/counts', [OfficeEmployeeController::class, 'counts']);

Route::get('office-employees/{officeEmployee}/documents/download-all', [OfficeEmployeeDocumentController::class, 'downloadAll']);
Route::delete('office-employees/{officeEmployee}/documents/{document}', [OfficeEmployeeController::class, 'destroyDocument']);

Route::apiResource('users', UserController::class);
Route::apiResource('resources', ResourceController::class);
Route::apiResource('informations', InformationController::class);
Route::get('informations/{information}/documents', [InformationDocumentController::class, 'index']);
Route::post('informations/{information}/documents', [InformationDocumentController::class, 'store']);
Route::delete('informations/{information}/documents/{document}', [InformationDocumentController::class, 'destroy']);
Route::get('informations/{information}/documents/zip', [InformationDocumentController::class, 'downloadAll']);

Route::apiResource('events', EventController::class);
Route::get('events/{event}/documents/zip', [EventDocumentController::class, 'downloadAll']);

Route::apiResource('captive-deliveries', CaptiveDeliveryController::class);
Route::get('captive-deliveries/{captiveDelivery}/documents', [CaptiveDeliveryDocumentController::class, 'index']);
Route::post('captive-deliveries/{captiveDelivery}/documents', [CaptiveDeliveryDocumentController::class, 'store']);
Route::delete('captive-deliveries/{captiveDelivery}/documents/{document}', [CaptiveDeliveryDocumentController::class, 'destroy']);
Route::get('captive-deliveries/{captiveDelivery}/documents/zip', [CaptiveDeliveryDocumentController::class, 'downloadAll']);

// ---------------------- FINANCE (NEW) ----------------------
// Account Types: name, note
Route::apiResource('account-types', AccountTypeController::class);

// Accounts: name, price, account_type_id, details
Route::apiResource('accounts', AccountController::class);

// ---------------------- FINANCE — Income & Expense ----------------------
Route::apiResource('incomes', IncomeController::class)->only(['index','store']);
Route::apiResource('expenses', ExpenseController::class)->only(['index','store']);

// ---------------------- FIFTHY FORMATS ----------------------
Route::apiResource('fasads', FasadController::class);
Route::apiResource('saeen-fasads', SaeenFasadController::class);
Route::apiResource('heists', HeistController::class);
Route::apiResource('captives', CaptiveController::class);
Route::apiResource('mulkanos', MulkanoController::class);
Route::apiResource('entry-exits', EntryExitController::class);
Route::apiResource('sarafyanos', SarafyanoController::class);
Route::apiResource('clubs', ClubsController::class);
Route::apiResource('mahajerats', MahajeratController::class);
Route::apiResource('soldiers', SoldierController::class);

// ---------------------- FIFTHY FORMAT ONE ----------------------
Route::apiResource('mawads', MawadController::class);
Route::apiResource('lost-mujremeens', LostMujremeenController::class);
Route::apiResource('old-soldiers-homes', OldSoldiersHomesController::class);
Route::apiResource('robbers', RobbersController::class);
Route::apiResource('non-responsable-people', NonResponsablePeopleController::class);
Route::apiResource('mashkok-homes', MashkokHomesController::class);
Route::apiResource('damage-places', DamagePlacesController::class);
Route::apiResource('damage-rasan-places', DamageRasanPlacesController::class);
Route::apiResource('mashkok-shops', MashkokShopsController::class);
Route::apiResource('luchakanos', LuchakanoController::class);

// ---------------------- FIFTHY FORMAT TWO ----------------------
Route::apiResource('schools', SchoolController::class);
Route::apiResource('hado-bus-stops', HadoBusStopController::class);
Route::apiResource('transport-companies', TransportCompanyController::class);
Route::apiResource('car-sales-companies', CarSalesCompanyController::class);
Route::apiResource('parkings', ParkingController::class);
Route::apiResource('banks', BankController::class);
Route::apiResource('apartments', ApartmentController::class);
Route::apiResource('rahnoma-mamlats', RahnomaMamlatController::class);
Route::apiResource('hosppitals', HosppitalController::class);
Route::apiResource('work-shops', WorkShopController::class);

// ---------------------- FIFTHY FORMAT THREE ----------------------
Route::apiResource('badmashanos', BadmashanoController::class);
Route::apiResource('sarayanos', SarayanoController::class);
Route::apiResource('hotels', HotelController::class);
Route::apiResource('hostels', HostelController::class);
Route::apiResource('tujarans', TujaranController::class);
Route::apiResource('mujahedinos', MujahedinoController::class);
Route::apiResource('emarat-offices', EmaratOfficeController::class);
Route::apiResource('old-jahadis', OldJahadiController::class);
Route::apiResource('mashkok-people', MashkokPeopleController::class);
Route::apiResource('madrasas', MadrasaController::class);

// ---------------------- FIFTHY FORMAT FOUR ----------------------
Route::apiResource('wakilanos', WakilanoController::class);
Route::apiResource('universities', UniversityController::class);
Route::apiResource('masjids', MasjidController::class);
Route::apiResource('highways', HighwayController::class);
Route::apiResource('companies', CompanyController::class);
Route::apiResource('donelostplaces', DoneLostPlaceController::class);
Route::apiResource('econemyprojects', EconemyProjectController::class);
Route::apiResource('criminalcaptureplaces', CriminalCapturePlaceController::class);
Route::apiResource('pumps', PumpController::class);
Route::apiResource('shoras', ShoraController::class);
Route::apiResource('governmentplaces', GovernmentPlaceController::class);
Route::apiResource('supermarkets', SuperMarketController::class);
Route::apiResource('parks', ParkController::class);
Route::apiResource('complexes', ComplexController::class);
Route::apiResource('deplomaticplaces', DeplomaticPlaceController::class);

// others
Route::get('office-employees/active',   [OfficeEmployeeController::class, 'active']);
Route::get('office-employees/inactive', [OfficeEmployeeController::class, 'inactive']);

// 🔹 Oil Accounts (list of tanks or oil holders)
Route::apiResource('oil-accounts', OilAccountController::class);

// 🔹 Income Oils (petrol coming in)
Route::apiResource('income-oils', IncomeOilController::class);

// 🔹 Expense Oils (petrol going out)
Route::apiResource('expense-oils', ExpenseOilController::class);
// routes/api.php
Route::get('oil-reports', [OilReportController::class, 'index']);

Route::prefix('finance-reports')->group(function () {
    Route::get('income',   [FinanceReportController::class, 'incomeReport']);
    Route::get('expense',  [FinanceReportController::class, 'expenseReport']);
    Route::get('accounts', [FinanceReportController::class, 'accountsReport']);
});
// dashboard
Route::prefix('dashboard/people')->group(function () {

    // Cards
    Route::get('cards', [PeopleDashboardController::class, 'cards']);

    // Charts
    Route::get('charts/employees-by-zone', [PeopleDashboardController::class, 'employeesByZone']);
    Route::get('charts/employees-with-events-by-zone', [PeopleDashboardController::class, 'employeesWithEventsByZone']);
    Route::get('charts/employees-with-informations-by-zone', [PeopleDashboardController::class, 'employeesWithInformationsByZone']);
    Route::get('charts/resources-over-time', [PeopleDashboardController::class, 'resourcesOverTime']);
    Route::get('charts/captive-deliveries-over-time', [PeopleDashboardController::class, 'captiveDeliveriesOverTime']);

    // Tables
    Route::get('tables/employee-activity', [PeopleDashboardController::class, 'employeeActivity']);
});


Route::prefix('dashboard/info-events')->group(function () {
    Route::get('/cards', [InfoEventDashboardController::class, 'cards']);
    Route::get('/charts/events-over-time', [InfoEventDashboardController::class, 'eventsOverTime']);
    Route::get('/charts/information-over-time', [InfoEventDashboardController::class, 'informationOverTime']);
    Route::get('/charts/employees-by-zone', [InfoEventDashboardController::class, 'employeesByZone']);
    Route::get('/tables/top-employees-by-resources', [InfoEventDashboardController::class, 'topEmployeesByResources']);
    Route::get('/tables/recent-events', [InfoEventDashboardController::class, 'recentEvents']);
    Route::get('/tables/recent-information', [InfoEventDashboardController::class, 'recentInformation']);
});



Route::prefix('dashboard/56-formats')->group(function () {
    Route::get('/summary',     [FFD::class, 'summary']);     // cards
    Route::get('/timeseries',  [FFD::class, 'timeseries']);  // series (daily/monthly/yearly)
    Route::get('/top',         [FFD::class, 'topResources']); // buckets
    Route::get('/recent',      [FFD::class, 'recent']);      // recent items
    Route::get('/combined',    [FFD::class, 'combined']);    // all-in-one
});


Route::prefix('dashboard/fifthyformatone')->group(function () {
    Route::get('/summary',        [FifthyFormatOneDashboardController::class, 'summary']);
    Route::get('/timeseries',     [FifthyFormatOneDashboardController::class, 'timeseries']);
    Route::get('/top-resources',  [FifthyFormatOneDashboardController::class, 'topResources']);
    Route::get('/recent',         [FifthyFormatOneDashboardController::class, 'recent']);
    Route::get('/combined',       [FifthyFormatOneDashboardController::class, 'combined']);
});




Route::prefix('dashboard/fifthyformatstwo')->group(function () {
    Route::get('summary',       [FifthyFormatsTwoDashboardController::class, 'summary']);
    Route::get('timeseries',    [FifthyFormatsTwoDashboardController::class, 'timeseries']);
    Route::get('top-resources', [FifthyFormatsTwoDashboardController::class, 'topResources']);
    Route::get('recent',        [FifthyFormatsTwoDashboardController::class, 'recent']);
    Route::get('combined',      [FifthyFormatsTwoDashboardController::class, 'combined']);

    // diagnostics (optional)
    Route::get('debug',         [FifthyFormatsTwoDashboardController::class, 'debug']);
    Route::get('diag',          [FifthyFormatsTwoDashboardController::class, 'diag']);
});



Route::get('/tiles/{filename}', [PmtilesController::class, 'serve'])
     ->where('filename', '.*');
Route::view('/map-afg', 'map-afg'); 
// ---------------------- PING / AUTH TEST ----------------------
Route::get('/ping', fn() => ['pong' => true]);
Route::get('/user', fn(Request $r) => $r->user())->middleware('auth:sanctum');
