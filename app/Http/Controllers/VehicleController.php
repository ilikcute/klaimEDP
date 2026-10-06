<?php

namespace App\Http\Controllers;

use App\Enums\VehicleType;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Vehicle::with('assignedUser:id,name,email');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%")
                  ->orWhere('plate_number', 'like', "%{$s}%");
            });
        }

        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $vehicles = $query->orderBy('code')->get();

        return response()->json([
            'success' => true,
            'data' => $vehicles,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:vehicles,code',
            'name' => 'required|string|max:100',
            'vehicle_type' => ['required', Rule::in(['MOTOR', 'CAR'])],
            'plate_number' => 'required|string|max:20',
            'assigned_user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        $vehicle = Vehicle::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'vehicle_type' => $validated['vehicle_type'],
            'plate_number' => strtoupper($validated['plate_number']),
            'assigned_user_id' => $validated['assigned_user_id'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data kendaraan dinas operasional berhasil ditambahkan.',
            'data' => $vehicle->load('assignedUser:id,name,email'),
        ], 201);
    }

    public function show(Vehicle $vehicle): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $vehicle->load('assignedUser:id,name,email'),
        ]);
    }

    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('vehicles', 'code')->ignore($vehicle->id)],
            'name' => 'sometimes|required|string|max:100',
            'vehicle_type' => ['sometimes', 'required', Rule::in(['MOTOR', 'CAR'])],
            'plate_number' => 'sometimes|required|string|max:20',
            'assigned_user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['code'])) {
            $validated['code'] = strtoupper($validated['code']);
        }
        if (isset($validated['plate_number'])) {
            $validated['plate_number'] = strtoupper($validated['plate_number']);
        }

        $vehicle->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data kendaraan dinas berhasil diperbarui.',
            'data' => $vehicle->fresh()->load('assignedUser:id,name,email'),
        ]);
    }

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $vehicle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kendaraan dinas berhasil dihapus.',
        ]);
    }
}
