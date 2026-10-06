<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EdpOperasionalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::whereHas('roles', function ($q) {
            $q->where('name', 'EDP_OPERASIONAL');
        })->with(['assignedVehicles']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('nik', 'like', "%{$s}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $users = $query->withCount('claims')
            ->withSum('claims as total_claim_amount', 'total_amount')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:255|unique:users,email',
            'nik' => 'nullable|string|max:50|unique:users,nik',
            'password' => 'required|string|min:6',
            'is_active' => 'boolean',
            'assigned_vehicle_id' => 'nullable|exists:vehicles,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'nik' => !empty($validated['nik']) ? $validated['nik'] : null,
            'password' => Hash::make($validated['password']),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Attach EDP_OPERASIONAL role
        $role = Role::firstOrCreate(['name' => 'EDP_OPERASIONAL'], [
            'display_name' => 'EDP Operasional',
            'is_active' => true,
        ]);
        $user->roles()->sync([$role->id]);

        // Connect vehicle if provided
        if (!empty($validated['assigned_vehicle_id'])) {
            Vehicle::where('id', $validated['assigned_vehicle_id'])
                ->update(['assigned_user_id' => $user->id]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Petugas EDP Operasional berhasil didaftarkan.',
            'data' => $user->fresh()->load('assignedVehicles'),
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->load(['assignedVehicles', 'roles']);
        $user->loadCount('claims');
        $user->total_claim_amount = (float)$user->claims()->sum('total_amount');

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($request->has('password') && empty($request->input('password'))) {
            $request->merge(['password' => null]);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'nik' => ['nullable', 'string', 'max:50', Rule::unique('users', 'nik')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'is_active' => 'boolean',
            'assigned_vehicle_id' => 'nullable',
        ]);

        $updateData = [];
        if (isset($validated['name'])) $updateData['name'] = $validated['name'];
        if (isset($validated['email'])) $updateData['email'] = strtolower($validated['email']);
        if (array_key_exists('nik', $validated)) $updateData['nik'] = !empty($validated['nik']) ? $validated['nik'] : null;
        if (!empty($validated['password'])) $updateData['password'] = Hash::make($validated['password']);
        if (isset($validated['is_active'])) $updateData['is_active'] = $validated['is_active'];

        $user->update($updateData);

        // Update assigned vehicle
        if (array_key_exists('assigned_vehicle_id', $validated)) {
            // Unassign current vehicles
            Vehicle::where('assigned_user_id', $user->id)->update(['assigned_user_id' => null]);
            // Assign new vehicle if selected
            if (!empty($validated['assigned_vehicle_id'])) {
                Vehicle::where('id', $validated['assigned_vehicle_id'])->update(['assigned_user_id' => $user->id]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Data EDP Operasional berhasil diperbarui.',
            'data' => $user->fresh()->load('assignedVehicles'),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $user = User::findOrFail($id);
        // Unassign any vehicles first
        Vehicle::where('assigned_user_id', $user->id)->update(['assigned_user_id' => null]);
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Akun EDP Operasional berhasil dinonaktifkan/dihapus.',
        ]);
    }
}
