<?php

namespace App\Http\Controllers;

use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Request;

// made with: php artisan make:controller ServiceController --api
class ServiceController extends Controller
{
    // GET /api/services
    // Customers get active services only. Admin adds ?all=1 to see all of them.
    public function index(Request $request)
    {
        if ($request->query('all') == 1) {
            $services = Service::all();
        } else {
            $services = Service::where('is_active', true)->get();
        }

        return ServiceResource::collection($services);
    }

    // POST /api/services  (admin)
    public function store(Request $request)
    {
        $data = $request->validate([
            'name_en'    => 'required|string|max:100',
            'name_ar'    => 'required|string|max:100',
            'unit_price' => 'required|numeric|min:1|max:1000',
        ]);

        $service = Service::create($data);
        $service->refresh(); // read it again so is_active gets its default value (true)

        return response()->json(new ServiceResource($service), 201);
    }

    // GET /api/services/{id}
    public function show($id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        return new ServiceResource($service);
    }

    // PUT /api/services/{id}  (admin)
    public function update(Request $request, $id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        $data = $request->validate([
            'name_en'    => 'sometimes|string|max:100',
            'name_ar'    => 'sometimes|string|max:100',
            'unit_price' => 'sometimes|numeric|min:1|max:1000',
        ]);

        $service->update($data);

        return new ServiceResource($service);
    }

    // DELETE /api/services/{id}  (admin)
    // Soft delete: we don't remove the row because old orders still use it.
    // We only set is_active = false, so customers can't choose it anymore.
    public function destroy($id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        $service->update(['is_active' => false]);

        return response()->json(['message' => 'Service deactivated'], 200);
    }

    // PATCH /api/services/{id}/activate  (admin)
    // Brings a deactivated service back.
    public function activate($id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        $service->update(['is_active' => true]);

        return response()->json(['message' => 'Service activated'], 200);
    }
}
