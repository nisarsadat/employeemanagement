<?php

namespace App\Http\Controllers\fifthyformatstow;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatstow\StoreSchoolRequest;
use App\Http\Requests\fifthyformatstow\UpdateSchoolRequest;
use App\Http\Resources\fifthyformatstow\SchoolResource;
use App\Models\fifthyformatstow\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = School::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->latest();

        if ($search) {
            $q->where(function ($qq) use ($search) {
                $qq->where('school_name', 'like', "%{$search}%")
                   ->orWhere('principle_name', 'like', "%{$search}%")
                   ->orWhere('principle_phone', 'like', "%{$search}%")
                   ->orWhere('province', 'like', "%{$search}%")
                   ->orWhere('district', 'like', "%{$search}%");
            });
        }

        return SchoolResource::collection($q->paginate($perPage));
    }

    public function store(StoreSchoolRequest $request)
    {
        $school = School::create($request->validated())
            ->load(['resource:id,name,code_number,phone_number']);

        return SchoolResource::make($school);
    }

    public function show(School $school)
    {
        $school->load(['resource:id,name,code_number,phone_number']);
        return SchoolResource::make($school);
    }

    public function update(UpdateSchoolRequest $request, School $school)
    {
        $school->update($request->validated());
        $school->load(['resource:id,name,code_number,phone_number']);

        return SchoolResource::make($school);
    }

    public function destroy(School $school)
    {
        $school->delete();
        return response()->noContent();
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        School::destroy($ids);
        return response()->noContent();
    }
}
