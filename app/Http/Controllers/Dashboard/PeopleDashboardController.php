<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\PeopleDashboardService;
use Illuminate\Http\Request;

class PeopleDashboardController extends Controller
{
    public function __construct(private PeopleDashboardService $svc) {}

    // ----- CARDS -----
    public function cards(Request $r)
    {
        return response()->json($this->svc->cards($r));
    }

    // ----- CHARTS -----
    public function employeesByZone(Request $r)
    {
        return response()->json($this->svc->employeesByZone($r));
    }

    public function employeesWithEventsByZone(Request $r)
    {
        return response()->json($this->svc->employeesWithEventsByZone($r));
    }

    public function employeesWithInformationsByZone(Request $r)
    {
        return response()->json($this->svc->employeesWithInformationsByZone($r));
    }

    public function resourcesOverTime(Request $r)
    {
        return response()->json($this->svc->resourcesOverTime($r));
    }

    public function captiveDeliveriesOverTime(Request $r)
    {
        return response()->json($this->svc->captiveDeliveriesOverTime($r));
    }

    // ----- TABLE -----
    public function employeeActivity(Request $r)
    {
        return response()->json($this->svc->employeeActivity($r));
    }
}
