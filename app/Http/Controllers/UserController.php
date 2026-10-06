<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['roles', 'directPermissions', 'assignedVehicles']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('nik', 'like', "%{$s}%")
                  ->orWhere('wilayah', 'like', "%{$s}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $users = $query->latest('id')->get()->map(function ($u) {
            $allPerms = $u->permissions();
            $pagePerms = $allPerms->filter(fn($p) => str_starts_with($p->name, 'page.'))->values();

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'nik' => $u->nik,
                'wilayah' => $u->wilayah,
                'is_active' => $u->is_active,
                'roles' => $u->roles->map(fn($r) => [
                    'id' => $r->id,
                    'name' => $r->name,
                    'display_name' => $r->display_name,
                ]),
                'direct_permissions' => $u->directPermissions->pluck('name'),
                'all_permissions' => $allPerms->pluck('name'),
                'accessible_pages' => $pagePerms->map(fn($p) => [
                    'name' => $p->name,
                    'display_name' => $p->display_name,
                ]),
                'assigned_vehicles' => $u->assignedVehicles,
                'created_at' => $u->created_at?->format('Y-m-d H:i:s'),
            ];
        });

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
            'wilayah' => 'nullable|string|max:100',
            'password' => 'required|string|min:6',
            'is_active' => 'boolean',
            'role' => 'required|string|exists:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'nik' => !empty($validated['nik']) ? $validated['nik'] : null,
            'wilayah' => !empty($validated['wilayah']) ? $validated['wilayah'] : null,
            'password' => Hash::make($validated['password']),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Attach Role
        $role = Role::where('name', $validated['role'])->first();
        if ($role) {
            $user->roles()->sync([$role->id]);
        }

        // Attach Direct Permissions (custom page access)
        if (!empty($validated['permissions'])) {
            $permIds = Permission::whereIn('name', $validated['permissions'])->pluck('id');
            $user->directPermissions()->sync($permIds);
        }

        return response()->json([
            'success' => true,
            'message' => "Pengguna {$user->name} berhasil ditambahkan.",
            'data' => $this->formatUserResponse($user),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->formatUserResponse($user),
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:150',
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'nik' => ['nullable', 'string', 'max:50', Rule::unique('users', 'nik')->ignore($user->id)],
            'wilayah' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:6',
            'is_active' => 'boolean',
            'role' => 'sometimes|required|string|exists:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $updateData = [];
        if (isset($validated['name'])) $updateData['name'] = $validated['name'];
        if (isset($validated['email'])) $updateData['email'] = strtolower($validated['email']);
        if (array_key_exists('nik', $validated)) $updateData['nik'] = !empty($validated['nik']) ? $validated['nik'] : null;
        if (array_key_exists('wilayah', $validated)) $updateData['wilayah'] = !empty($validated['wilayah']) ? $validated['wilayah'] : null;
        if (!empty($validated['password'])) $updateData['password'] = Hash::make($validated['password']);
        if (isset($validated['is_active'])) $updateData['is_active'] = $validated['is_active'];

        $user->update($updateData);

        // Update Role if provided
        if (isset($validated['role'])) {
            $role = Role::where('name', $validated['role'])->first();
            if ($role) {
                $user->roles()->sync([$role->id]);
            }
        }

        // Update Direct Permissions (Assign User A can access which pages)
        if (array_key_exists('permissions', $validated)) {
            $permIds = Permission::whereIn('name', $validated['permissions'] ?? [])->pluck('id');
            $user->directPermissions()->sync($permIds);
        }

        return response()->json([
            'success' => true,
            'message' => "Data pengguna {$user->name} dan hak akses berhasil diperbarui.",
            'data' => $this->formatUserResponse($user->fresh()),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => "Pengguna {$user->name} berhasil dinonaktifkan/dihapus.",
        ]);
    }

    public function permissionsList(): JsonResponse
    {
        $permissions = Permission::all()->map(function ($p) {
            $category = 'Aksi Lainnya';
            if (str_starts_with($p->name, 'page.')) {
                $category = 'Hak Akses Halaman & Menu';
            } elseif (str_starts_with($p->name, 'claim.') || str_starts_with($p->name, 'receipt.') || str_starts_with($p->name, 'document.')) {
                $category = 'Otorisasi Workflow & Klaim';
            } elseif (str_starts_with($p->name, 'report.') || str_starts_with($p->name, 'analytics.')) {
                $category = 'Laporan & Analisa';
            } elseif (str_starts_with($p->name, 'user.')) {
                $category = 'Manajemen Pengguna';
            }

            return [
                'id' => $p->id,
                'name' => $p->name,
                'display_name' => $p->display_name,
                'description' => $p->description,
                'category' => $category,
                'is_page' => str_starts_with($p->name, 'page.'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $permissions,
        ]);
    }

    public function rolesList(): JsonResponse
    {
        $roles = Role::with('permissions')->get()->map(function ($r) {
            return [
                'id' => $r->id,
                'name' => $r->name,
                'display_name' => $r->display_name,
                'description' => $r->description,
                'permissions' => $r->permissions->pluck('name'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $roles,
        ]);
    }

    public function updateRolePermissions(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $permIds = Permission::whereIn('name', $validated['permissions'])->pluck('id');
        $role->permissions()->sync($permIds);

        return response()->json([
            'success' => true,
            'message' => "Hak akses default untuk role {$role->display_name} berhasil disimpan.",
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->fresh()->permissions->pluck('name'),
            ],
        ]);
    }

    protected function formatUserResponse(User $user): array
    {
        $user->load(['roles.permissions', 'directPermissions', 'assignedVehicles']);
        $allPerms = $user->permissions();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'nik' => $user->nik,
            'wilayah' => $user->wilayah,
            'is_active' => $user->is_active,
            'roles' => $user->roles->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'display_name' => $r->display_name,
            ]),
            'direct_permissions' => $user->directPermissions->pluck('name'),
            'all_permissions' => $allPerms->pluck('name'),
            'accessible_pages' => $allPerms->filter(fn($p) => str_starts_with($p->name, 'page.'))->values()->map(fn($p) => [
                'name' => $p->name,
                'display_name' => $p->display_name,
            ]),
            'assigned_vehicles' => $user->assignedVehicles,
        ];
    }
}
