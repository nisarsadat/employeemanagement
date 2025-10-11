<?php

namespace App\Http\Controllers\fifthyFormats;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\fifthyFormats\Fasad;
use App\Http\Resources\fifthyFormats\FasadResource;
// If you use a single request for both create/update, swap these two lines
use App\Http\Requests\fifthyFormats\StoreFasadRequest;
use App\Http\Requests\fifthyFormats\UpdateFasadRequest;

class FasadController extends Controller
{
    /**
     * GET /fasads
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 10);
        $search  = $request->input('search');

        $q = Fasad::query()->with('resource')->latest();

        if ($search) {
            $q->where('name', 'like', "%{$search}%");
        }

        return FasadResource::collection($q->paginate($perPage));
    }

    /**
     * POST /fasads
     */
    public function store(StoreFasadRequest $request)
    {
        $data = $request->validated();

        // handle picture upload (string path like OfficeEmployee)
        if ($request->hasFile('picture')) {
            $path = $request->file('picture')->store('uploads/fasads', 'public');
            $data['picture'] = "storage/{$path}";
        }

        $fasad = Fasad::create($data)->load('resource');

        return FasadResource::make($fasad);
    }

    /**
     * GET /fasads/{fasad}
     */
    public function show(Fasad $fasad)
    {
        $fasad->load('resource');
        return FasadResource::make($fasad);
    }

    /**
     * PUT /fasads/{fasad}
     */
    public function update(UpdateFasadRequest $request, Fasad $fasad)
    {
        $data = $request->validated();

        // replace picture if a new file comes in
        if ($request->hasFile('picture')) {
            if ($fasad->picture && str_starts_with($fasad->picture, 'storage/')) {
                $diskPath = str_replace('storage/', '', $fasad->picture);
                Storage::disk('public')->delete($diskPath);
            }

            $path = $request->file('picture')->store('uploads/fasads', 'public');
            $data['picture'] = "storage/{$path}";
        } else {
            // keep existing string if not sent
            unset($data['picture']);
        }

        $fasad->update($data);
        $fasad->load('resource');

        return FasadResource::make($fasad);
    }

    /**
     * DELETE /fasads/{fasad}
     */
    public function destroy(Fasad $fasad)
    {
        if ($fasad->picture && str_starts_with($fasad->picture, 'storage/')) {
            $diskPath = str_replace('storage/', '', $fasad->picture);
            Storage::disk('public')->delete($diskPath);
        }

        $fasad->delete();

        return response()->noContent();
    }

    /**
     * Optional bulk delete to match your style:
     * POST /fasads/bulk-delete  with { "ids": [1,2,3] }
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        Fasad::destroy($ids);
        return response()->noContent();
    }
}
