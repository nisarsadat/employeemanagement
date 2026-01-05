<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobTypeRequest;
use App\Http\Requests\UpdateJobTypeRequest;
use App\Http\Resources\JobTypeResource;
use App\Models\JobType;
use Illuminate\Http\Request;

class JobTypeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('perPage', 25);
        $perPage = max(1, min(200, $perPage));

        $search = trim((string) $request->query('search', ''));

        $query = JobType::query();

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        $query->latest('id');

        return JobTypeResource::collection(
            $query->paginate($perPage)
        );
    }

    public function store(StoreJobTypeRequest $request)
    {
        $jobType = JobType::create($request->validated());
        return new JobTypeResource($jobType);
    }

    public function show(JobType $jobType)
    {
        return new JobTypeResource($jobType);
    }

    public function update(UpdateJobTypeRequest $request, JobType $jobType)
    {
        $jobType->update($request->validated());
        return new JobTypeResource($jobType);
    }

    public function destroy(JobType $jobType)
    {
        $jobType->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
}
