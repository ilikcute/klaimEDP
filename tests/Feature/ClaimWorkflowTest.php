<?php

namespace Tests\Feature;

use App\Enums\ClaimState;
use App\Enums\ClaimType;
use App\Models\BbmStandard;
use App\Models\Claim;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClaimWorkflowTest extends TestCase
{
    public function test_user_can_login_and_receive_token(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'operasional@klaimedp.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_claim_creation_and_receipt_total_recalculation(): void
    {
        $user = User::where('email', 'operasional@klaimedp.com')->first();
        $token = $user->createToken('test')->plainTextToken;

        // 1. Create claim
        $createRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/claims', [
                'claim_type' => 'TOL',
                'claim_date' => '2026-10-05',
                'description' => 'Test claim operasional tol',
            ]);

        $createRes->assertStatus(201);
        $claimId = $createRes->json('data.id');

        // 2. Add receipt 1
        $r1 = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/claims/{$claimId}/receipts", [
                'merchant' => 'Gerbang Tol Tebet',
                'amount' => 15000,
                'transaction_date' => '2026-10-05',
            ]);
        $r1->assertStatus(201);

        // 3. Add receipt 2
        $r2 = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/claims/{$claimId}/receipts", [
                'merchant' => 'Parkir Gedung A',
                'amount' => 25000,
                'transaction_date' => '2026-10-05',
            ]);
        $r2->assertStatus(201);

        // Check total is 40000
        $claim = Claim::find($claimId);
        $this->assertEquals(40000.00, (float)$claim->total_amount);
        $this->assertEquals(ClaimState::DRAFT, $claim->current_state);

        // 4. Submit claim
        $subRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/claims/{$claimId}/submit");
        $subRes->assertStatus(200);

        $claim->refresh();
        $this->assertEquals(ClaimState::SUBMITTED, $claim->current_state);
    }

    public function test_management_approval_any_of_policy_satisfaction(): void
    {
        $requester = User::where('email', 'operasional@klaimedp.com')->first();
        $claim = Claim::create([
            'claim_number' => Claim::generateClaimNumber(),
            'requester_id' => $requester->id,
            'claim_type' => ClaimType::BBM,
            'claim_date' => '2026-10-05',
            'description' => 'Test claim for approval',
            'total_amount' => 500000.00,
            'current_state' => ClaimState::MANAGEMENT_APPROVAL,
            'submitted_at' => now()->subDay(),
        ]);

        $bm = User::where('email', 'bm@klaimedp.com')->first();
        $token = $bm->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/claims/{$claim->id}/management-approval", [
                'decision' => 'APPROVE',
                'notes' => 'Disetujui oleh BM cabang',
            ]);

        $response->assertStatus(200);
        $claim->refresh();
        $this->assertEquals(ClaimState::APPROVED, $claim->current_state);
    }

    public function test_bbm_claim_creation_with_odometer_and_distance_calculation(): void
    {
        $user = User::where('email', 'operasional@klaimedp.com')->first();
        $token = $user->createToken('test_bbm')->plainTextToken;

        $vehicle = Vehicle::first();

        // Submit claim BBM with odometer data
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/claims', [
                'claim_type' => 'BBM',
                'claim_date' => '2026-10-06',
                'description' => 'Perjalanan dinas supervisi dari Toko Cabang A ke Toko Cabang B (Isi BBM di SPBU Pertamina)',
                'vehicle_id' => $vehicle->id,
                'last_odometer_km' => 12500,
                'current_odometer_km' => 12620,
                'distance_km' => 120,
                'fuel_liter' => 3.5,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        $claimId = $response->json('data.id');

        $this->assertDatabaseHas('claim_bbm_details', [
            'claim_id' => $claimId,
            'vehicle_id' => $vehicle->id,
            'last_odometer_km' => 12500.00,
            'current_odometer_km' => 12620.00,
            'distance_km' => 120.00,
            'fuel_liter' => 3.50,
        ]);

        // Check vehicles endpoint returns updated last_odometer_km
        $vehRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/vehicles');

        $vehRes->assertStatus(200);
        $vehicleData = collect($vehRes->json('data'))->firstWhere('id', $vehicle->id);
        $this->assertEquals(12620.0, (float)$vehicleData['last_odometer_km']);
    }

    public function test_claim_document_upload_and_inline_view(): void
    {
        \Illuminate\Support\Facades\Storage::fake('local');

        $user = User::where('email', 'operasional@klaimedp.com')->first();
        $token = $user->createToken('test_doc')->plainTextToken;
        $claim = Claim::first();

        $file = \Illuminate\Http\UploadedFile::fake()->image('nota_struk_spbu.jpg');

        $uploadRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/claims/{$claim->id}/documents", [
                'file' => $file,
                'document_type' => 'RECEIPT',
                'latitude' => -7.7956,
                'longitude' => 110.3695,
                'gps_accuracy_meters' => 12.5,
                'captured_at' => now(),
            ]);

        $uploadRes->assertStatus(201);
        $docId = $uploadRes->json('data.id');

        $this->assertDatabaseHas('claim_documents', [
            'id' => $docId,
            'latitude' => -7.79560000,
            'longitude' => 110.36950000,
            'gps_accuracy_meters' => 12.5,
        ]);

        // Test view inline with token query parameter
        $viewRes = $this->get("/api/documents/{$docId}/view?token={$token}");
        $viewRes->assertStatus(200);
        $viewRes->assertHeader('Content-Disposition', 'inline; filename="nota_struk_spbu.jpg"');
    }
}
