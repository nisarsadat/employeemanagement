<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'unique:job_types,name'],
        ], [
            'name.required' => 'د وظیفې ډول نوم ضروري دی.',
            'name.unique'   => 'دا د وظیفې ډول نوم مخکې نه ثبت شوی دی.',
        ]);

        $jobType = JobType::create($data);

        return new JobTypeResource($jobType);
    }

    public function show(JobType $jobType)
    {
        return new JobTypeResource($jobType);
    }

    public function update(Request $request, JobType $jobType)
    {
        $data = $request->validate([
            'name' => ['required', 'unique:job_types,name,' . $jobType->id],
        ], [
            'name.required' => 'د وظیفې ډول نوم ضروري دی.',
            'name.unique'   => 'دا د وظیفې ډول نوم مخکې نه ثبت شوی دی.',
        ]);

        $jobType->update($data);

        return new JobTypeResource($jobType);
    }

    public function destroy(JobType $jobType)
    {
        $jobType->delete();
        return response()->json(['message' => 'په بریالیتوب سره حذف شو.']);
    }
}