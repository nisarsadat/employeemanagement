<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ---------------------- Auth ----------------------
use App\Http\Controllers\AuthController;

// ---------------------- Core APIs ----------------------
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\OfficeEmployeeController;
use App\Http\Controllers\InformationController;
use App\Http\Controllers\InformationDocumentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventDocumentController;
use App\Http\Controllers\CaptiveDeliveryController;
use App\Http\Controllers\CaptiveDeliveryDocumentController;

// ✅ FINANCE
use App\Http\Controllers\Api\AccountTypeController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\ExpenseController;

// ---------------------- Fifty Formats ----------------------
// (56 formats)
use App\Http\Controllers\fifthyFormats\{
    FasadController,
    SaeenFasadController,
    HeistController,
    CaptiveController,
    MulkanoController,
    EntryExitController,
    SarafyanoController,
    ClubsController,
    JawasisController,
    MahajeratController,
    SoldierController
};

// FIFTHY FORMAT ONE
use App\Http\Controllers\fifthyformatone\{
    MawadController,
    LostMujremeenController,
    OldSoldiersHomesController,
    RobbersController,
    NonResponsablePeopleController,
    MashkokHomesController,
    DamagePlacesController,
    DamageRasanPlacesController,
    MashkokShopsController,
    LuchakanoController
};

// FIFTHY FORMAT THREE
use App\Http\Controllers\fifthyformatsthree\{
    BadmashanoController,
    EmaratOfficeController,
    HostelController,
    HotelController,
    MadrasaController,
    MashkokPeopleController,
    MujahedinoController,
    OldJahadiController,
    SarayanoController,
    TujaranController
};

// FIFTHY FORMAT TWO
use App\Http\Controllers\fifthyformatstow\{
    SchoolController,
    HadoBusStopController,
    TransportCompanyController,
    CarSalesCompanyController,
    ParkingController,
    BankController,
    ApartmentController,
    RahnomaMamlatController,
    HosppitalController,
    WorkShopController
};

// FIFTHY FORMAT FOUR
use App\Http\Controllers\Api\fifthyformatfour\{
    CheckpointController,
    MasjidController,
    HighwayController,
    CompanyController,
    DoneLostPlaceController,
    EconemyProjectController,
    CriminalCapturePlaceController,
    PumpController,
    ShoraController,
    GovernmentPlaceController,
    SuperMarketController,
    ParkController,
    ComplexController,
    DeplomaticPlaceController,
    UniversityController,
    WakilanoController
};

// ---------------------- Dashboards & Misc ----------------------
use App\Http\Controllers\Dashboard\FifthyFormatOneDashboardController;
use App\Http\Controllers\Dashboard\InfoEventDashboardController;
use App\Http\Controllers\Dashboard\PeopleDashboardController;
use App\Http\Controllers\OfficeEmployeeDocumentController;
use App\Http\Controllers\OilAccountController;
use App\Http\Controllers\IncomeOilController;
use App\Http\Controllers\ExpenseOilController;
use App\Http\Controllers\OilReportController;
use App\Http\Controllers\FinanceReportController;
use App\Http\Controllers\PmtilesController;
use App\Http\Controllers\Dashboard\FiftyFormatsDashboardController as FFD;
use App\Http\Controllers\Dashboard\FifthyFormatsTwoDashboardController;
use App\Http\Controllers\Dashboard\FifthyFormatThreeDashboardController as FF3;
use App\Http\Controllers\Dashboard\FifthyFormatFourDashboardController as FF4;

// ---------------------- RBAC (roles/permissions) ----------------------
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RbacController;

use App\Http\Controllers\Api\ActiveInactiveOfficeEmployeeController as AE; // ✅ NEW
use App\Http\Controllers\Api\ChatAttachmentController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\EventControllerReport;
use App\Http\Controllers\Api\EventReportController;
use App\Http\Controllers\Api\EventSpecificTypeController;
use App\Http\Controllers\Api\EventTypeController;
use App\Http\Controllers\Api\ExpenseOilController as ApiExpenseOilController;
use App\Http\Controllers\Api\FifthyFormatsCountController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\FormatZoneController;
use App\Http\Controllers\Api\HawzaController;
use App\Http\Controllers\Api\MapPointController;
use App\Http\Controllers\Api\Reports\FiftyFormatsReportController as ReportsFiftyFormatsReportController;
use App\Http\Controllers\Api\OfficeEmployeeControllerReport;
use App\Models\OfficeEmployee;
use App\Http\Controllers\Reports\FiftyFormatsReportController;
use App\Http\Controllers\Api\InformationReportController;
use App\Http\Controllers\Api\JenayeeTypeController;
use App\Http\Controllers\Api\JobTypeController;
use App\Http\Controllers\Api\KhayratReportController;
use App\Http\Controllers\Api\MarjaController;
use App\Http\Controllers\Api\MawqafJahadiController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\OilSummaryController;
use App\Http\Controllers\Api\OrganController;
use App\Http\Controllers\Api\OutgoingIncomingController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\Reports\Foul\DailyFoulReportController;
use App\Http\Controllers\Api\Reports\Foul\MonthlyFoulReportController;
use App\Http\Controllers\Api\Reports\Foul\QuarterlyFoulReportController;
use App\Http\Controllers\Api\Reports\Foul\WeeklyFoulReportController;
use App\Http\Controllers\Api\Reports\Foul\YearlyFoulReportController;
use App\Http\Controllers\Api\ResourceActivityController;
use App\Http\Controllers\Api\ResourceActivityTypeController;
use App\Http\Controllers\Api\SeyaseeTypeController;
use App\Http\Controllers\Api\ZoneController;
use App\Http\Controllers\CaptiveDocumentController;
use App\Http\Controllers\DocsTypeController;
use App\Http\Controllers\EmployeeManagementDashboardController;

/*
|--------------------------------------------------------------------------
| Helper: protectResource(uri, module, Controller::class)
| Maps REST verbs to CRUD actions. (No permission middleware; sanctum only)
| Adds numeric constraint on {id} so paths like "active" never bind to {id}
|--------------------------------------------------------------------------
*/
if (!function_exists('protectResource')) {
    function protectResource(string $uri, string $module, string $controller): void
    {
        // index (GET /)
        Route::get($uri, [$controller, 'index']);

        // show (GET /{id})
        Route::get($uri.'/{id}', [$controller, 'show'])
            ->whereNumber('id');

        // store (POST /)
        Route::post($uri, [$controller, 'store']);

        // update (PUT/PATCH /{id})
        Route::match(['put','patch'], $uri.'/{id}', [$controller, 'update'])
            ->whereNumber('id');

        // destroy (DELETE /{id})
        Route::delete($uri.'/{id}', [$controller, 'destroy'])
            ->whereNumber('id');
    }
}

/*
|--------------------------------------------------------------------------
| PUBLIC (no auth) routes
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::get('/tiles/{filename}', [PmtilesController::class, 'serve'])->where('filename', '.*');
Route::view('/map-afg', 'map-afg');

Route::get('/ping', fn() => ['pong' => true]);

/*
|--------------------------------------------------------------------------
| AUTHENTICATED & PROTECTED routes (Sanctum only)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Who am I + logout
    Route::get('/user', fn(Request $r) => $r->user());
    Route::post('/logout', [AuthController::class, 'logout']);

    // ---------------------- RBAC admin (still behind auth only) ----------------------
    Route::apiResource('roles',        RoleController::class);
    Route::apiResource('permissions',  PermissionController::class);

    // Lightweight lists
    Route::get('rbac/roles',        [RbacController::class, 'roles']);
    Route::get('rbac/permissions',  [RbacController::class, 'permissions']);

    // ---------------------- Custom bindings ----------------------
    Route::bind('office_employee', function ($value) {
        if (!ctype_digit((string) $value)) abort(404);
        return OfficeEmployee::findOrFail((int) $value);
    });

    // ---------------------- Users ----------------------
    Route::get   ('users',           [UserController::class, 'index']);
    Route::post  ('users',           [UserController::class, 'store']);
    Route::get   ('users/{user}',    [UserController::class, 'show']);    // 👈 {user}
    Route::match(['put','patch'], 'users/{user}', [UserController::class, 'update']); // 👈 {user}
    Route::delete('users/{user}',    [UserController::class, 'destroy']); // 👈 {user}

    // ---------- Office Employees ----------
    // Special activity routes BEFORE the generic resource to avoid ambiguity
    Route::get('office-employees/activity-counts', [AE::class, 'counts']);
    Route::get('office-employees/active',         [AE::class, 'active']);
    Route::get('office-employees/inactive',       [AE::class, 'inactive']);

    Route::get('officeemployees/date-options', [OfficeEmployeeController::class, 'dateOptions']);

    protectResource('office-employees', 'office_employees', OfficeEmployeeController::class);


    Route::get('officeemployees/date-options', [OfficeEmployeeController::class, 'dateOptions']);

    Route::get('office-employees/{officeEmployee}/documents/download-all', [OfficeEmployeeDocumentController::class, 'downloadAll']);
    Route::delete('office-employees/{officeEmployee}/documents/{document}', [OfficeEmployeeController::class, 'destroyDocument']);

    // resources (API) => module: resources_api
    protectResource('resources', 'resources_api', ResourceController::class);

    // informations (API) => module: informations_api
    protectResource('informations', 'informations_api', InformationController::class);
    Route::get   ('informations/{information}/documents',               [InformationDocumentController::class, 'index']);
    Route::post  ('informations/{information}/documents',               [InformationDocumentController::class, 'store']);
    Route::delete('informations/{information}/documents/{document}',    [InformationDocumentController::class, 'destroy']);
    Route::get   ('informations/{information}/documents/zip',           [InformationDocumentController::class, 'downloadAll']);
    Route::get('informations/date-options', [InformationController::class, 'dateOptions']);
    // events (API) => module: events_api
    protectResource('events', 'events_api', EventController::class);
    Route::get('events/{event}/documents/zip', [EventDocumentController::class, 'downloadAll']);
    Route::get('events/date-options', [EventController::class, 'dateOptions']);

    // captive-deliveries => module: captive_deliveries
    protectResource('captive-deliveries', 'captive_deliveries', CaptiveDeliveryController::class);
    Route::get   ('captive-deliveries/{captiveDelivery}/documents',              [CaptiveDeliveryDocumentController::class, 'index']);
    Route::post  ('captive-deliveries/{captiveDelivery}/documents',              [CaptiveDeliveryDocumentController::class, 'store']);
    Route::delete('captive-deliveries/{captiveDelivery}/documents/{document}',   [CaptiveDeliveryDocumentController::class, 'destroy']);
    Route::get   ('captive-deliveries/{captiveDelivery}/documents/zip',          [CaptiveDeliveryDocumentController::class, 'downloadAll']);

       // ---------------------- Fifty Formats (NO PREFIX, apiResource) ----------------------

    // Main Fifty Formats (10)
    Route::apiResource('fasads', FasadController::class);
    // ✅ zip download (picture + documents)
    Route::get('fasads/{id}/download-zip', [FasadController::class, 'downloadZip']);

    // (optional) delete single document by id
    Route::delete('fasad-documents/{docId}', [FasadController::class, 'deleteDocument']);
    Route::apiResource('saeen-fasads', SaeenFasadController::class);
    Route::get('saeen-fasads/{saeenFasad}/download-zip', [SaeenFasadController::class, 'downloadZip']);
    Route::apiResource('heists', HeistController::class);
    Route::get('heists/{heist}/documents-zip', [HeistController::class, 'downloadDocumentsZip']);
    Route::apiResource('captives', CaptiveController::class);
    Route::get('captives/{captive}/documents/zip', [CaptiveDocumentController::class, 'downloadAll']);

    Route::apiResource('mulkanos', MulkanoController::class);
    Route::apiResource('entry-exits', EntryExitController::class);
    Route::apiResource('sarafyanos', SarafyanoController::class);
    Route::apiResource('clubs', ClubsController::class);
    Route::apiResource('mahajerats', MahajeratController::class);
    Route::apiResource('soldiers', SoldierController::class);

    // Fifthy Format One (10)
    Route::apiResource('mawads', MawadController::class);



    Route::prefix('lost-mujremeens')->group(function () {
        Route::get('/', [LostMujremeenController::class, 'index']);
        Route::get('{lostMujremeen}', [LostMujremeenController::class, 'show']);
        Route::post('/', [LostMujremeenController::class, 'store']);

        // ✅ Update must exist (your error said PUT not supported)
        Route::put('{lostMujremeen}', [LostMujremeenController::class, 'update']);
        Route::post('{lostMujremeen}', [LostMujremeenController::class, 'update']); // optional if you send _method
        Route::delete('{lostMujremeen}', [LostMujremeenController::class, 'destroy']);

        // ✅ ZIP download
        Route::get('{lostMujremeen}/documents-zip', [LostMujremeenController::class, 'downloadDocumentsZip']);
    });


    Route::apiResource('old-soldiers-homes', OldSoldiersHomesController::class);
    Route::apiResource('robbers', RobbersController::class);
    Route::apiResource('non-responsable-people', NonResponsablePeopleController::class);
    Route::apiResource('mashkok-homes', MashkokHomesController::class);
    Route::get('/mashkok-homes/{mashkok_home}/documents/zip', [MashkokHomesController::class, 'documentsZip']);
    Route::apiResource('damage-places', DamagePlacesController::class);
    Route::apiResource('damage-rasan-places', DamageRasanPlacesController::class);
    Route::apiResource('mashkok-shops', MashkokShopsController::class);
    Route::apiResource('luchakanos', LuchakanoController::class);

    // Fifthy Format Two (10)
    Route::apiResource('schools', SchoolController::class);
    Route::apiResource('hado-bus-stops', HadoBusStopController::class);
    Route::apiResource('transport-companies', TransportCompanyController::class);
    Route::apiResource('car-sales-companies', CarSalesCompanyController::class);
    Route::apiResource('parkings', ParkingController::class);
    Route::apiResource('banks', BankController::class);
    Route::apiResource('apartments', ApartmentController::class);
    Route::apiResource('rahnoma-mamlats', RahnomaMamlatController::class);
    Route::apiResource('hosppitals', HosppitalController::class); // keep typo name if DB/table uses it
    Route::apiResource('work-shops', WorkShopController::class);

    // Fifthy Format Three (10)
    Route::apiResource('badmashanos', BadmashanoController::class);
    Route::apiResource('emarat-offices', EmaratOfficeController::class);
    Route::apiResource('hostels', HostelController::class);
    Route::apiResource('hotels', HotelController::class);
    Route::apiResource('madrasas', MadrasaController::class);
    Route::apiResource('mashkok-people', MashkokPeopleController::class);
    Route::apiResource('mujahedinos', MujahedinoController::class);
    Route::apiResource('old-jahadis', OldJahadiController::class);
    Route::apiResource('sarayanos', SarayanoController::class);
    Route::apiResource('tujarans', TujaranController::class);

    // Fifthy Format Four (16)
    Route::apiResource('checkpoints', CheckpointController::class);
    Route::apiResource('masjids', MasjidController::class);
    Route::apiResource('highways', HighwayController::class);
    Route::apiResource('companies', CompanyController::class);
    Route::apiResource('done-lost-places', DoneLostPlaceController::class);
    Route::apiResource('econemy-projects', EconemyProjectController::class);
    Route::apiResource('criminal-capture-places', CriminalCapturePlaceController::class);
    Route::apiResource('pumps', PumpController::class);
    Route::apiResource('shoras', ShoraController::class);
    Route::apiResource('governmentplaces', GovernmentPlaceController::class);
    Route::apiResource('supermarkets', SuperMarketController::class);
    Route::apiResource('parks', ParkController::class);
    Route::apiResource('complexes', ComplexController::class);
    Route::apiResource('deplomaticplaces', DeplomaticPlaceController::class);
    Route::apiResource('universities', UniversityController::class);
    Route::apiResource('wakilanos', WakilanoController::class);

    // ---------------------- Finance ----------------------
    protectResource('account-types', 'account_types', AccountTypeController::class);
    Route::get('/accounts/years', [AccountController::class, 'years']);
    Route::get('/incomes/years', [IncomeController::class, 'years']);
    Route::get('/finances/years', [FinanceController::class, 'years']);
    Route::apiResource('finances', FinanceController::class);
    Route::apiResource('incomes', IncomeController::class);
    Route::apiResource('accounts', AccountController::class);


    // Oil
    protectResource('oil-accounts', 'oil_accounts',  OilAccountController::class);
    protectResource('income-oils',  'income_oils',   IncomeOilController::class);
    protectResource('expense-oils', 'expense_oils',  ExpenseOilController::class);
    
    Route::get('/oil/summary', [OilSummaryController::class, 'index']);
    Route::get('/expense-oils', [ExpenseOilController::class, 'index']);
    Route::get('/expense-oils/years', [ExpenseOilController::class, 'years']);
    Route::get('/expense-oils/{expenseOil}', [ExpenseOilController::class, 'show']);
    Route::post('/expense-oils', [ExpenseOilController::class, 'store']);
    Route::put('/expense-oils/{expenseOil}', [ExpenseOilController::class, 'update']);
    Route::delete('/expense-oils/{expenseOil}', [ExpenseOilController::class, 'destroy']);

    Route::get('oil-reports', [OilReportController::class, 'index']);

    Route::prefix('finance-reports')->group(function () {
        Route::get('income',   [FinanceReportController::class, 'incomeReport']);
        Route::get('expense',  [FinanceReportController::class, 'expenseReport']);
        Route::get('accounts', [FinanceReportController::class, 'accountsReport']);
    });

    // ---------------------- Dashboards (Sanctum only) ----------------------
    Route::prefix('dashboard/people')->group(function () {
        Route::get('cards', [PeopleDashboardController::class, 'cards']);
        Route::get('charts/employees-by-zone',                   [PeopleDashboardController::class, 'employeesByZone']);
        Route::get('charts/employees-with-events-by-zone',       [PeopleDashboardController::class, 'employeesWithEventsByZone']);
        Route::get('charts/employees-with-informations-by-zone', [PeopleDashboardController::class, 'employeesWithInformationsByZone']);
        Route::get('charts/resources-over-time',                 [PeopleDashboardController::class, 'resourcesOverTime']);
        Route::get('charts/captive-deliveries-over-time',        [PeopleDashboardController::class, 'captiveDeliveriesOverTime']);
        Route::get('tables/employee-activity',                   [PeopleDashboardController::class, 'employeeActivity']);
    });

    Route::prefix('dashboard/info-events')->group(function () {
        Route::get('/cards',                         [InfoEventDashboardController::class, 'cards']);
        Route::get('/charts/events-over-time',       [InfoEventDashboardController::class, 'eventsOverTime']);
        Route::get('/charts/information-over-time',  [InfoEventDashboardController::class, 'informationOverTime']);
        Route::get('/charts/employees-by-zone',      [InfoEventDashboardController::class, 'employeesByZone']);
        Route::get('/tables/top-employees-by-resources', [InfoEventDashboardController::class, 'topEmployeesByResources']);
        Route::get('/tables/recent-events',          [InfoEventDashboardController::class, 'recentEvents']);
        Route::get('/tables/recent-information',     [InfoEventDashboardController::class, 'recentInformation']);
    });

    Route::prefix('dashboard/56-formats')->group(function () {
        Route::get('/summary',    [FFD::class, 'summary']);
        Route::get('/timeseries', [FFD::class, 'timeseries']);
        Route::get('/top',        [FFD::class, 'topResources']);
        Route::get('/recent',     [FFD::class, 'recent']);
        Route::get('/combined',   [FFD::class, 'combined']);
    });

    Route::prefix('dashboard/fifthyformatone')->group(function () {
        Route::get('/summary',       [FifthyFormatOneDashboardController::class, 'summary']);
        Route::get('/timeseries',    [FifthyFormatOneDashboardController::class, 'timeseries']);
        Route::get('/top-resources', [FifthyFormatOneDashboardController::class, 'topResources']);
        Route::get('/recent',        [FifthyFormatOneDashboardController::class, 'recent']);
        Route::get('/combined',      [FifthyFormatOneDashboardController::class, 'combined']);
    });

    Route::prefix('dashboard/fifthyformatstwo')->group(function () {
        Route::get('summary',       [FifthyFormatsTwoDashboardController::class, 'summary']);
        Route::get('timeseries',    [FifthyFormatsTwoDashboardController::class, 'timeseries']);
        Route::get('top-resources', [FifthyFormatsTwoDashboardController::class, 'topResources']);
        Route::get('recent',        [FifthyFormatsTwoDashboardController::class, 'recent']);
        Route::get('combined',      [FifthyFormatsTwoDashboardController::class, 'combined']);
        Route::get('debug',         [FifthyFormatsTwoDashboardController::class, 'debug']);
        Route::get('diag',          [FifthyFormatsTwoDashboardController::class, 'diag']);
    });

    Route::prefix('dashboard/ff3')->group(function () {
        Route::get('/summary',    [FF3::class, 'summary']);
        Route::get('/timeseries', [FF3::class, 'timeseries']);
        Route::get('/top',        [FF3::class, 'topResources']);
        Route::get('/zones',      [FF3::class, 'topZones']);
        Route::get('/provinces',  [FF3::class, 'topProvinces']);
        Route::get('/breakdown',  [FF3::class, 'breakdown']);
        Route::get('/recent',     [FF3::class, 'recent']);
        Route::get('/combined',   [FF3::class, 'combined']);
    });

    Route::prefix('dashboard/ff4')->group(function () {
        Route::get('combined',  [FF4::class, 'combined']);
        Route::get('breakdown', [FF4::class, 'breakdown']);
    });

    // RBAC helpers
    Route::put ('roles/{role}/permissions', [RbacController::class, 'syncRolePermissions']);
    Route::post('roles/{role}/permissions', [RbacController::class, 'syncRolePermissions']);
    Route::get('rbac/roles',        [RbacController::class, 'roles']);
    Route::get('rbac/permissions',  [RbacController::class, 'permissions']);
    Route::get('/auth/me',          [AuthController::class, 'me']);

    // Map points (for your PMTiles map)
    Route::apiResource('map-points', MapPointController::class)
        ->only(['index', 'store', 'destroy']);

    // reports 
        // ---------------------- Reports (56 formats) ----------------------

    Route::get('reports/fifty-formats', [ReportsFiftyFormatsReportController::class, 'index']);

    Route::get('office-employees-report/activity-report', [OfficeEmployeeControllerReport::class, 'activityReport']);

    // in routes/api.php or similar:
    Route::get(
        'office-employees-report/{id}/{relation}',
        [OfficeEmployeeControllerReport::class, 'employeeRelation']
    );


    Route::prefix('events-report')->group(function () {
        Route::get('/', [EventControllerReport::class, 'index']);
        Route::get('/counts', [EventControllerReport::class, 'counts']);
        Route::get('/activity-report', [EventControllerReport::class, 'activityReport']);
        Route::get('/{id}', [EventControllerReport::class, 'show']);
    });

    Route::get('information-report/activity-report', [InformationReportController::class, 'activityReport']);
    Route::get('fifty-formats/counts', [FifthyFormatsCountController::class, 'index']);
        // ---------------------- Employee Management Dashboard ----------------------
    Route::prefix('dashboard/employee-management')->group(function () {
        // Cards: totals (employees, resources, active/inactive, info, events, captive, money, oil)
        Route::get('/cards', [EmployeeManagementDashboardController::class, 'cards']);

        // Time series for graphs (resources, informations, events) with ?from= & ?to=
        Route::get('/timeseries', [EmployeeManagementDashboardController::class, 'timeseries']);

        // 56 formats categorized by hawza
        Route::get('/formats-by-hawza', [EmployeeManagementDashboardController::class, 'formatsByHawza']);
    });

        // ---------------------- Notifications ----------------------
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    // 🔹 Get unread count
    // GET /api/notifications/unread-count
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
        ->name('notifications.unreadCount');

    // 🔹 Mark ONE notification as read
    // POST /api/notifications/{notification}/mark-as-read
    Route::post('/notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.markAsRead');

    // 🔹 Mark ALL notifications as read
    // POST /api/notifications/mark-all-as-read
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.markAllAsRead');

    Route::delete('/notifications/clear', [NotificationController::class, 'clearAll']);


    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/chat/users', [ChatController::class, 'users']);
        Route::get('/chat/conversations', [ChatController::class, 'conversations']);
        Route::get('/chat/conversations/user/{user}', [ChatController::class, 'conversationWithUser']);

        Route::post('/chat/messages', [ChatController::class, 'sendMessage']); // multipart
        Route::delete('/chat/messages/{message}', [ChatController::class, 'deleteMessage']);

        Route::post('/chat/conversations/{conversation}/read', [ChatController::class, 'markAsRead']);
        Route::post('/chat/conversations/read-all', [ChatController::class, 'readAllConversations']);
        Route::post('/chat/conversations/{conversation}/clear', [ChatController::class, 'clearConversation']);
        Route::post('/chat/chats/delete-all', [ChatController::class, 'deleteAllChats']);
        Route::get('/chat/conversations/{conversation}/search', [ChatController::class, 'searchMessages']);

        Route::post('/chat/ping', [ChatController::class, 'ping']);
    });



    // the resource for the active and inactive 

    Route::get('/resource-activity/counts', [ResourceActivityController::class, 'counts']);
    Route::get('/resource-activity', [ResourceActivityController::class, 'index']);
    Route::get('/resource-activity/{id}', [ResourceActivityController::class, 'show']);
    Route::get('resources/date-options', [ResourceController::class, 'dateOptions']);

    // daily report + monthly report + yearly report

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/reports/foul/daily', DailyFoulReportController::class);
        Route::get('/reports/foul/monthly', MonthlyFoulReportController::class);
        Route::get('/reports/foul/yearly', YearlyFoulReportController::class);
        Route::get('/reports/foul/weekly', WeeklyFoulReportController::class);
        Route::get('/reports/foul/quarterly', QuarterlyFoulReportController::class);
        Route::get('/reports/foul/all-time', YearlyFoulReportController::class);
    });


    // dashboard 

    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Outgoing/Incoming CRUD

    Route::apiResource('outgoing-incomings', OutgoingIncomingController::class);


    // ✅ ZIP Download (ALL docs for one record)
    Route::get('/outgoing-incomings/{id}/documents-zip', [OutgoingIncomingController::class, 'downloadDocumentsZip']);


    // system settings

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('provinces', ProvinceController::class);
        Route::apiResource('districts', DistrictController::class);
        Route::apiResource('zones', ZoneController::class);
        Route::apiResource('hawzas', HawzaController::class);
        Route::get('/event-types/options', [EventTypeController::class, 'options']);
        Route::apiResource('/event-types', EventTypeController::class);
        Route::apiResource('/event-specific-types', EventSpecificTypeController::class);
        Route::apiResource('jenayee-types', JenayeeTypeController::class);
        Route::apiResource('seyasee-types', SeyaseeTypeController::class);
        Route::apiResource('organs', OrganController::class);
        Route::apiResource('job-types', JobTypeController::class);
        Route::apiResource('resource-activity-types', ResourceActivityTypeController::class);
        Route::apiResource('mawqaf-jahadis', MawqafJahadiController::class);

        // khayrat report 
        Route::apiResource('khayrat-reports', KhayratReportController::class);
        Route::apiResource('format-zones', FormatZoneController::class);
    });
    Route::apiResource('docs-types', DocsTypeController::class);
    Route::apiResource('marjas', MarjaController::class);


    Route::apiResource('jawasis', JawasisController::class);
    Route::post('jawasis/bulk-delete', [JawasisController::class, 'bulkDelete']);
    Route::delete('jawasis/document/{id}', [JawasisController::class, 'deleteDocument']);
    Route::get('jawasis/{id}/download-zip', [JawasisController::class, 'downloadZip']);

});
