<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'ADMIN'], ['display_name' => 'Admin', 'is_active' => true]);
        $this->admin = User::firstOrCreate(
            ['email' => 'admin.test@klaimedp.com'],
            ['name' => 'Admin Test', 'password' => bcrypt('password'), 'is_active' => true]
        );
        $this->admin->roles()->sync([$adminRole->id]);
    }

    public function test_can_list_users_with_permissions()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/users');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id', 'name', 'email', 'is_active', 'roles',
                        'direct_permissions', 'all_permissions', 'accessible_pages'
                    ]
                ]
            ]);
    }

    public function test_can_create_user_and_assign_custom_page_permissions()
    {
        $email = 'user.custom.' . rand(1000, 9999) . '@klaimedp.com';
        $nik = 'NIK-' . rand(10000, 99999);
        $payload = [
            'name' => 'User Custom Access',
            'email' => $email,
            'nik' => $nik,
            'wilayah' => 'Jakarta Barat',
            'password' => 'secret123',
            'is_active' => true,
            'role' => 'EDP_OPERASIONAL',
            'permissions' => ['page.dashboard', 'page.reports', 'page.tasks'],
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/users', $payload);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'User Custom Access',
                    'email' => $email,
                    'nik' => $nik,
                    'wilayah' => 'Jakarta Barat',
                ]
            ]);

        $createdUser = User::where('email', $email)->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals($nik, $createdUser->nik);
        $this->assertEquals('Jakarta Barat', $createdUser->wilayah);
        $this->assertTrue($createdUser->hasPermission('page.reports'));
        $this->assertTrue($createdUser->hasPermission('page.tasks'));
    }

    public function test_can_update_user_and_change_page_permissions()
    {
        $user = User::create([
            'name' => 'Staff To Edit',
            'email' => 'staff.edit.' . rand(1000, 9999) . '@klaimedp.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $edpRole = Role::where('name', 'EDP_OPERASIONAL')->first();
        if ($edpRole) {
            $user->roles()->sync([$edpRole->id]);
        }

        // Initially does NOT have page.audit
        $this->assertFalse($user->hasPermission('page.audit'));

        // Assign page.audit directly to this user
        $response = $this->actingAs($this->admin)->putJson("/api/users/{$user->id}", [
            'name' => 'Staff To Edit Updated',
            'role' => 'EDP_OPERASIONAL',
            'permissions' => ['page.audit', 'page.reports'],
        ]);

        $response->assertOk();

        $user->refresh();
        $this->assertTrue($user->hasPermission('page.audit'));
        $this->assertTrue($user->hasPermission('page.reports'));
    }

    public function test_cannot_delete_self()
    {
        $response = $this->actingAs($this->admin)->deleteJson("/api/users/{$this->admin->id}");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_can_delete_other_user()
    {
        $user = User::create([
            'name' => 'User To Delete',
            'email' => 'user.del.' . rand(1000, 9999) . '@klaimedp.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->deleteJson("/api/users/{$user->id}");

        $response->assertOk();
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_can_get_permissions_and_roles_list()
    {
        $permsRes = $this->actingAs($this->admin)->getJson('/api/users/permissions');
        $permsRes->assertOk()->assertJsonStructure(['success', 'data']);

        $rolesRes = $this->actingAs($this->admin)->getJson('/api/users/roles');
        $rolesRes->assertOk()->assertJsonStructure(['success', 'data']);
    }
}
