<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\OfficeEmployee;
use App\Http\Requests\OfficeEmployeeRequest;
use App\Http\Resources\OfficeEmployeeResource;

class OfficeEmployeeController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('can:viewOfficeEmployee')->only(['index','show']);
    //     $this->middleware('can:createOfficeEmployee')->only('store');
    //     $this->middleware('can:updateOfficeEmployee')->only('update');
    //     $this->middleware('can:deleteOfficeEmployee')->only('destroy');
    // }

    /**
     * GET /office-employees
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $employees = OfficeEmployee::query()
            ->search($search)
            ->latest()
            ->paginate($perPage);

        return OfficeEmployeeResource::collection($employees);
    }

    /**
     * POST /office-employees
     */
    public function store(OfficeEmployeeRequest $request)
    {
        $data = $request->validated();

        // handle picture upload
        if ($request->hasFile('picture')) {
            $path = $request->file('picture')->store('uploads/office-employees', 'public');
            $data['picture'] = "storage/{$path}";
        }

        $employee = OfficeEmployee::create($data);

        return OfficeEmployeeResource::make($employee);
    }

    /**
     * GET /office-employees/{officeEmployee}
     */
    public function show(OfficeEmployee $officeEmployee)
    {
        return OfficeEmployeeResource::make($officeEmployee);
    }

    /**
     * PUT /office-employees/{officeEmployee}
     */
    public function update(OfficeEmployeeRequest $request, OfficeEmployee $officeEmployee)
    {
        $data = $request->validated();

        // picture replacement (delete old if exists)
        if ($request->hasFile('picture')) {
            if ($officeEmployee->picture && str_starts_with($officeEmployee->picture, 'storage/')) {
                $diskPath = str_replace('storage/', '', $officeEmployee->picture);
                Storage::disk('public')->delete($diskPath);
            }
            $path = $request->file('picture')->store('uploads/office-employees', 'public');
            $data['picture'] = "storage/{$path}";
        } else {
            // do not null picture if not sent
            unset($data['picture']);
        }

        $officeEmployee->update($data);

        return OfficeEmployeeResource::make($officeEmployee);
    }

    /**
     * DELETE /office-employees/{officeEmployee}
     */
    public function destroy(OfficeEmployee $officeEmployee)
    {
        if ($officeEmployee->picture && str_starts_with($officeEmployee->picture, 'storage/')) {
            $diskPath = str_replace('storage/', '', $officeEmployee->picture);
            Storage::disk('public')->delete($diskPath);
        }

        $officeEmployee->delete();

        // keep same style: noContent() like your sample
        return response()->noContent();
    }

    /**
     * Optional: bulk delete (same style as your sample)
     * POST /office-employees/bulk-delete  with  { "employeeIds": [1,2,3] }
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('employeeIds', []);
        OfficeEmployee::destroy($ids);
        return response()->noContent();
    }
}
