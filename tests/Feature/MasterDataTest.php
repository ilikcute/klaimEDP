<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    protected User $admin;
    protected User $edpUser;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'ADMIN'], ['display_name' => 'Admin', 'is_active' => true]);
        $edpRole = Role::firstOrCreate(['name' => 'EDP_OPERASIONAL'], ['display_name' => 'EDP Operasional', 'is_active' => true]);

        $this->admin = User::firstOrCreate(
            ['email' => 'testadmin@klaimedp.com'],
            ['name' => 'Test Admin', 'password' => bcrypt('password'), 'is_active' => true]
        );
        $this->admin->roles()->sync([$adminRole->id]);

        $this->edpUser = User::firstOrCreate(
            ['email' => 'testedp@klaimedp.com'],
            ['name' => 'Test EDP Lapangan', 'password' => bcrypt('password'), 'is_active' => true]
        );
        $this->edpUser->roles()->sync([$edpRole->id]);
    }

    public function test_can_list_master_vehicles()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/master/vehicles');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'code', 'name', 'vehicle_type', 'plate_number', 'is_active']
                ]
            ]);
    }

    public function test_can_create_vehicle_and_assign_to_edp_operasional()
    {
        $code = 'TEST-VEH-' . rand(100, 999);
        $payload = [
            'code' => $code,
            'name' => 'Yamaha Gear EDP Test',
            'vehicle_type' => 'MOTOR',
            'plate_number' => 'B 9999 TST',
            'assigned_user_id' => $this->edpUser->id,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/master/vehicles', $payload);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'data' => [
                    'code' => $code,
                    'assigned_user_id' => $this->edpUser->id,
                ]
            ]);

        $this->assertDatabaseHas('vehicles', [
            'code' => $code,
            'assigned_user_id' => $this->edpUser->id,
        ]);
    }

    public function test_can_list_master_edp_operasional()
    {
        $response = $this->actingAs($this->admin)->getJson('/api/master/edp-operasional');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'email', 'assigned_vehicles']
                ]
            ]);
    }

    public function test_can_create_edp_operasional_and_link_vehicle()
    {
        $vehicle = Vehicle::first();

        $email = 'edp.baru.' . uniqid() . '@klaimedp.com';
        $nik = 'EDP-' . strtoupper(substr(uniqid(), -6));
        $payload = [
            'name' => 'Asep EDP Baru',
            'email' => $email,
            'nik' => $nik,
            'password' => 'secret123',
            'is_active' => true,
            'assigned_vehicle_id' => $vehicle ? $vehicle->id : null,
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/master/edp-operasional', $payload);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Asep EDP Baru',
                    'email' => $email,
                    'nik' => $nik,
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'nik' => $nik,
        ]);
    }
}
