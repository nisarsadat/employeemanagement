<?php

namespace App\Http\Controllers\Api\fifthyformatfour;

use App\Http\Controllers\Controller;
use App\Http\Requests\fifthyformatfour\StoreCheckpointRequest;
use App\Http\Requests\fifthyformatfour\UpdateCheckpointRequest;
use App\Http\Resources\fifthyformatfour\CheckpointResource;
use App\Models\fifthyformatfour\Checkpoint;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CheckpointController extends Controller
{
    public function index(Request $request)
    {
        $query = Checkpoint::query()->with(['resource:id,name,code_number,phone_number']);

        if ($s = trim((string) $request->query('search', ''))) {
            $query->where(function (Builder $q) use ($s) {
                $q->where('checkpoint_name', 'like', "%$s%")
                  ->orWhere('organ', 'like', "%$s%")
                  ->orWhere('province', 'like', "%$s%")
                  ->orWhere('village', 'like', "%$s%");
            });
        }

        $perPage = $request->query('perPage');
        $page    = $request->query('page');

        $perPageInt = filter_var($perPage, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);
        $pageInt    = filter_var($page,    FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]);

        // Fallback → return ALL when missing/invalid
        if ($perPageInt === false || $pageInt === false) {
            return CheckpointResource::collection($query->orderByDesc('id')->get());
        }

        $rows = $query->orderByDesc('id')->paginate($perPageInt, ['*'], 'page', $pageInt);
        return CheckpointResource::collection($rows);
    }

    public function store(StoreCheckpointRequest $request)
    {
        $row = Checkpoint::create($request->validated());
        $row->load(['resource:id,name,code_number,phone_number']);
        return new CheckpointResource($row);
    }

    public function show(Checkpoint $checkpoint)
    {
        $checkpoint->load('resource');
        return CheckpointResource::make($checkpoint);
    }

    public function update(UpdateCheckpointRequest $request, Checkpoint $checkpoint)
    {
        $checkpoint->update($request->validated());
        $checkpoint->load(['resource:id,name,code_number,phone_number']);
        return new CheckpointResource($checkpoint);
    }

    public function destroy(Checkpoint $checkpoint)
    {
        $checkpoint->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
