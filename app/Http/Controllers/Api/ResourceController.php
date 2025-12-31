<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResourceRequest;
use App\Http\Resources\ResourceResource;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationService;

class ResourceController extends Controller
{
    public function index(Request $req)
    {
        $search = trim((string) $req->get('search', ''));

        $q = Resource::query()
            ->with(['officeEmployee:id,name,code_number,father_name'])
            // ✅ NEW: sum of all finance money for each resource
            ->withSum('finances as payed_money', 'money_amount_number')
            ->when($search !== '', function ($w) use ($search) {
                $w->where(function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('grand_father_name', 'like', "%{$search}%")
                        ->orWhere('nick_name', 'like', "%{$search}%")
                        ->orWhere('code_number', 'like', "%{$search}%")
                        ->orWhere('tazkira', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->latest('id');

        // If perPage is provided and valid (>0), paginate (honors ?page=)
        $perPage = $req->filled('perPage') ? (int) $req->get('perPage') : null;

        if ($perPage !== null && $perPage > 0) {
            $data = $q->paginate($perPage);

            return ResourceResource::collection($data)
                ->additional([
                    'meta' => [
                        'total' => $data->total(),
                    ],
                ]);
        }

        // Otherwise return all
        $rows = $q->get();

        return ResourceResource::collection($rows)
            ->additional([
                'meta' => [
                    'total' => $rows->count(),
                ],
            ]);
    }

    public function show($id)
    {
        $row = Resource::query()
            ->with(['officeEmployee:id,name,code_number,father_name,nick_name'])
            // ✅ NEW: sum for single record too
            ->withSum('finances as payed_money', 'money_amount_number')
            ->findOrFail($id);

        return new ResourceResource($row);
    }

    public function store(ResourceRequest $req)
    {
        $data = $req->validated();
        $user = $req->user(); // 👈 who did this action

        // Image upload (optional)
        if ($req->hasFile('picture')) {
            $path = $req->file('picture')->store('uploads/resources', 'public');
            $data['picture'] = "storage/{$path}";
        }

        $row = Resource::create($data);

        // 🔔 create notification (ONLY code_number in message)
        NotificationService::record(
            $user,
            module: 'resources',
            action: 'created',
            title: 'منبع جدید ثبت شد',
            message: 'کد منبع: ' . ($row->code_number ?? ''),
            routeName: 'resources.edit',              // 👈 edit page route
            routeParams: ['id' => $row->id],
            level: 'success',
        );

        return (new ResourceResource(
            $row->loadMissing('officeEmployee:id,name,code_number,father_name')
        ))
            ->additional(['message' => 'Created'])
            ->response()
            ->setStatusCode(201);
    }

    public function update(ResourceRequest $req, $id)
    {
        $row  = Resource::findOrFail($id);
        $data = $req->validated();
        $user = $req->user(); // 👈

        // Replace image if a new one is provided
        if ($req->hasFile('picture')) {
            if ($row->picture && str_starts_with($row->picture, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $row->picture));
            }

            $path = $req->file('picture')->store('uploads/resources', 'public');
            $data['picture'] = "storage/{$path}";
        } else {
            // don't null out existing if not sent
            unset($data['picture']);
        }

        $row->fill($data)->save();

        // 🔔 notification for update (ONLY code_number in message)
        NotificationService::record(
            $user,
            module: 'resources',
            action: 'updated',
            title: 'منبع اصلاح شد',
            message: 'کد منبع: ' . ($row->code_number ?? ''),
            routeName: 'resources',              // if you want edit page: 'resources.edit'
            routeParams: ['id' => $row->id],
            level: 'info',
        );

        return new ResourceResource(
            $row->loadMissing('officeEmployee:id,name,code_number,father_name')
        );
    }

    public function destroy(Request $request, $id)
    {
        $row  = Resource::findOrFail($id);
        $user = $request->user(); // 👈

        // keep code_number before delete
        $code = $row->code_number;

        if ($row->picture && str_starts_with($row->picture, 'storage/')) {
            Storage::disk('public')->delete(str_replace('storage/', '', $row->picture));
        }

        $row->delete();

        // 🔔 notification for delete (ONLY code_number in message)
        NotificationService::record(
            $user,
            module: 'resources',
            action: 'deleted',
            title: 'منبع حذف شد',
            message: 'کد منبع: ' . ($code ?? ''),
            routeName: null,          // no specific page for delete
            routeParams: [],
            level: 'warning',
        );

        return response()->json(['message' => 'Deleted']);
    }
}
