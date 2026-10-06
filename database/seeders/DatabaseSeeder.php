<?php

namespace Database\Seeders;

use App\Enums\ApprovalRule;
use App\Enums\ClaimState;
use App\Enums\ClaimType;
use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Enums\PaymentStatus;
use App\Enums\VehicleType;
use App\Models\ApprovalPolicy;
use App\Models\BbmStandard;
use App\Models\Claim;
use App\Models\ClaimBbmDetail;
use App\Models\ClaimDocument;
use App\Models\ClaimHealthDetail;
use App\Models\ClaimReceipt;
use App\Models\ClaimStatusHistory;
use App\Models\HealthPolicy;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SlaInstance;
use App\Models\SlaPolicy;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkflowAction;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Models\WorkflowStage;
use App\Models\WorkflowTransition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. ROLES
        $rolesData = [
            ['name' => 'ADMIN', 'display_name' => 'System Administrator', 'description' => 'Akses penuh administrasi dan konfigurasi sistem'],
            ['name' => 'EDP_OPERASIONAL', 'display_name' => 'EDP Operasional', 'description' => 'Pembuat klaim operasional lapangan'],
            ['name' => 'EDP_ADM', 'display_name' => 'EDP Administrasi', 'description' => 'Pencatatan dan review administratif awal'],
            ['name' => 'EDP_SUPERVISOR', 'display_name' => 'EDP Supervisor', 'description' => 'Verifikasi teknis dan operasional klaim'],
            ['name' => 'EDP_MANAGER', 'display_name' => 'EDP Manager', 'description' => 'Verifikasi manajerial dan kepatuhan anggaran'],
            ['name' => 'FINANCE', 'display_name' => 'GA & Finance', 'description' => 'Verifikasi keuangan dan eksekusi pembayaran'],
            ['name' => 'DBM', 'display_name' => 'Deputy Branch Manager (DBM)', 'description' => 'Management approver cabang'],
            ['name' => 'BM', 'display_name' => 'Branch Manager (BM)', 'description' => 'Management approver cabang'],
        ];

        $roles = [];
        foreach ($rolesData as $r) {
            $roles[$r['name']] = Role::updateOrCreate(['name' => $r['name']], $r);
        }

        // 2. PERMISSIONS
        $permissionsList = [
            'claim.view',
            'claim.view.own',
            'claim.create',
            'claim.update',
            'claim.update.own_draft',
            'claim.submit',
            'claim.submit.own',
            'claim.review',
            'claim.verify',
            'claim.finance_verify',
            'claim.approve',
            'claim.reject',
            'claim.request_revision',
            'claim.pay',
            'claim.manage',
            'receipt.create',
            'receipt.update.own',
            'receipt.manage',
            'receipt.view',
            'document.upload.own',
            'document.view.own',
            'document.upload',
            'document.view',
            'document.manage',
            'history.view',
            'analytics.view',
            'report.view',
            'report.export',
            'report.payment',
            'audit.view',
        ];

        foreach ($permissionsList as $pName) {
            $perm = Permission::updateOrCreate(
                ['name' => $pName],
                ['display_name' => ucwords(str_replace('.', ' ', $pName))]
            );
            $roles['ADMIN']->permissions()->syncWithoutDetaching([$perm->id]);
        }

        // Assign specific permissions to roles
        $roles['EDP_OPERASIONAL']->permissions()->sync(
            Permission::whereIn('name', [
                'claim.create', 'claim.view.own', 'claim.update.own_draft', 'claim.submit.own',
                'receipt.create', 'receipt.update.own', 'document.upload.own', 'document.view.own', 'history.view'
            ])->pluck('id')
        );

        $roles['EDP_ADM']->permissions()->sync(
            Permission::whereIn('name', [
                'claim.create', 'claim.view', 'claim.update', 'claim.review', 'claim.request_revision',
                'claim.reject', 'receipt.manage', 'receipt.view', 'document.manage', 'document.view', 'history.view'
            ])->pluck('id')
        );

        $roles['EDP_SUPERVISOR']->permissions()->sync(
            Permission::whereIn('name', [
                'claim.view', 'claim.verify', 'claim.request_revision', 'claim.reject',
                'receipt.view', 'document.view', 'history.view', 'report.view'
            ])->pluck('id')
        );

        $roles['EDP_MANAGER']->permissions()->sync(
            Permission::whereIn('name', [
                'claim.view', 'claim.verify', 'claim.request_revision', 'claim.reject',
                'receipt.view', 'document.view', 'history.view', 'analytics.view', 'report.view', 'report.export'
            ])->pluck('id')
        );

        $roles['FINANCE']->permissions()->sync(
            Permission::whereIn('name', [
                'claim.view', 'claim.finance_verify', 'claim.verify', 'claim.request_revision', 'claim.reject',
                'claim.pay', 'receipt.view', 'document.view', 'document.upload', 'history.view', 'report.view', 'report.payment', 'report.export'
            ])->pluck('id')
        );

        $roles['DBM']->permissions()->sync(
            Permission::whereIn('name', [
                'claim.view', 'claim.approve', 'claim.request_revision', 'claim.reject',
                'receipt.view', 'document.view', 'history.view', 'analytics.view', 'report.view'
            ])->pluck('id')
        );

        $roles['BM']->permissions()->sync(
            Permission::whereIn('name', [
                'claim.view', 'claim.approve', 'claim.request_revision', 'claim.reject',
                'receipt.view', 'document.view', 'history.view', 'analytics.view', 'report.view'
            ])->pluck('id')
        );

        // 3. USERS
        $usersData = [
            ['name' => 'Super Administrator', 'email' => 'admin@klaimedp.com', 'nik' => 'ADM-001', 'wilayah' => 'Head Office (Pusat)', 'role' => 'ADMIN'],
            ['name' => 'Budi Operasional', 'email' => 'operasional@klaimedp.com', 'nik' => 'EDP-1001', 'wilayah' => 'Jakarta Barat', 'role' => 'EDP_OPERASIONAL'],
            ['name' => 'Siti Admin EDP', 'email' => 'adm@klaimedp.com', 'nik' => 'EDP-1002', 'wilayah' => 'Jakarta Barat', 'role' => 'EDP_ADM'],
            ['name' => 'Ahmad Supervisor', 'email' => 'supervisor@klaimedp.com', 'nik' => 'SPV-2001', 'wilayah' => 'DKI Jakarta', 'role' => 'EDP_SUPERVISOR'],
            ['name' => 'Rendra Manager EDP', 'email' => 'manager@klaimedp.com', 'nik' => 'MGR-3001', 'wilayah' => 'DKI Jakarta', 'role' => 'EDP_MANAGER'],
            ['name' => 'Dewi Finance', 'email' => 'finance@klaimedp.com', 'nik' => 'FIN-4001', 'wilayah' => 'Head Office (Pusat)', 'role' => 'FINANCE'],
            ['name' => 'Hendra DBM', 'email' => 'dbm@klaimedp.com', 'nik' => 'DBM-5001', 'wilayah' => 'Cabang Jakarta 1', 'role' => 'DBM'],
            ['name' => 'Bambang BM', 'email' => 'bm@klaimedp.com', 'nik' => 'BM-6001', 'wilayah' => 'Cabang Jakarta 1', 'role' => 'BM'],
        ];

        $users = [];
        foreach ($usersData as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'nik' => $u['nik'],
                    'wilayah' => $u['wilayah'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
            $user->roles()->sync([$roles[$u['role']]->id]);
            $users[$u['role']] = $user;
        }

        // 4. VEHICLES
        $operasionalUser = $users['EDP_OPERASIONAL'];
        $vehiclesData = [
            ['code' => 'MTR-01', 'name' => 'Honda Beat EDP 01', 'vehicle_type' => VehicleType::MOTOR, 'plate_number' => 'B 3456 EDP', 'assigned_user_id' => $operasionalUser->id],
            ['code' => 'MTR-02', 'name' => 'Yamaha NMAX EDP 02', 'vehicle_type' => VehicleType::MOTOR, 'plate_number' => 'B 4567 EDP', 'assigned_user_id' => null],
            ['code' => 'MOB-01', 'name' => 'Toyota Avanza EDP 01', 'vehicle_type' => VehicleType::CAR, 'plate_number' => 'B 1234 EDP', 'assigned_user_id' => $operasionalUser->id],
            ['code' => 'MOB-02', 'name' => 'Daihatsu Gran Max EDP 02', 'vehicle_type' => VehicleType::CAR, 'plate_number' => 'B 2345 EDP', 'assigned_user_id' => null],
        ];

        $vehicles = [];
        foreach ($vehiclesData as $v) {
            $vehicles[] = Vehicle::updateOrCreate(['code' => $v['code']], $v);
        }

        // 5. BBM STANDARDS
        BbmStandard::updateOrCreate(
            ['vehicle_type' => VehicleType::MOTOR, 'effective_from' => '2026-01-01'],
            ['standard_km_per_liter' => 40.00, 'is_active' => true]
        );
        BbmStandard::updateOrCreate(
            ['vehicle_type' => VehicleType::CAR, 'effective_from' => '2026-01-01'],
            ['standard_km_per_liter' => 13.00, 'is_active' => true]
        );

        // 6. HEALTH POLICY
        $healthPolicy = HealthPolicy::updateOrCreate(
            ['name' => 'Kebijakan Klaim Kesehatan Tahunan 2026', 'effective_from' => '2026-01-01'],
            ['annual_limit' => 10000000.00, 'is_active' => true]
        );

        // 7. WORKFLOW DEFINITION & STAGES
        $wfDef = WorkflowDefinition::updateOrCreate(
            ['code' => 'REIMBURSEMENT_V1', 'version' => 1],
            ['name' => 'Standard EDP Reimbursement Workflow', 'is_active' => true]
        );

        $stagesData = [
            ['code' => 'DRAFT', 'name' => 'Draft Klaim', 'sequence' => 1, 'stage_type' => 'INITIAL'],
            ['code' => 'SUBMITTED', 'name' => 'Diajukan', 'sequence' => 2, 'stage_type' => 'REVIEW'],
            ['code' => 'EDP_REVIEW', 'name' => 'Review EDP Adm', 'sequence' => 3, 'stage_type' => 'REVIEW'],
            ['code' => 'SUPERVISOR_VERIFICATION', 'name' => 'Verifikasi Supervisor', 'sequence' => 4, 'stage_type' => 'VERIFICATION'],
            ['code' => 'MANAGER_VERIFICATION', 'name' => 'Verifikasi Manager', 'sequence' => 5, 'stage_type' => 'VERIFICATION'],
            ['code' => 'FINANCE_VERIFICATION', 'name' => 'Verifikasi Finance', 'sequence' => 6, 'stage_type' => 'VERIFICATION'],
            ['code' => 'MANAGEMENT_APPROVAL', 'name' => 'Approval Management', 'sequence' => 7, 'stage_type' => 'APPROVAL'],
            ['code' => 'APPROVED', 'name' => 'Disetujui', 'sequence' => 8, 'stage_type' => 'APPROVAL'],
            ['code' => 'PAYMENT_PROCESS', 'name' => 'Proses Pembayaran', 'sequence' => 9, 'stage_type' => 'PAYMENT'],
            ['code' => 'PAID', 'name' => 'Selesai Dibayar', 'sequence' => 10, 'stage_type' => 'COMPLETED'],
            ['code' => 'NEED_REVISION', 'name' => 'Perlu Revisi', 'sequence' => 11, 'stage_type' => 'REVIEW'],
            ['code' => 'REJECTED', 'name' => 'Ditolak', 'sequence' => 12, 'stage_type' => 'TERMINAL'],
            ['code' => 'CANCELLED', 'name' => 'Dibatalkan', 'sequence' => 13, 'stage_type' => 'TERMINAL'],
        ];

        $stages = [];
        foreach ($stagesData as $s) {
            $stages[$s['code']] = WorkflowStage::updateOrCreate(
                ['workflow_definition_id' => $wfDef->id, 'code' => $s['code']],
                $s
            );
        }

        // Transitions
        $transitions = [
            ['stage' => 'DRAFT', 'action' => 'SUBMIT', 'from' => 'DRAFT', 'to' => 'SUBMITTED', 'perm' => 'claim.submit'],
            ['stage' => 'NEED_REVISION', 'action' => 'RESUBMIT', 'from' => 'NEED_REVISION', 'to' => 'SUBMITTED', 'perm' => 'claim.submit'],
            ['stage' => 'SUBMITTED', 'action' => 'START_REVIEW', 'from' => 'SUBMITTED', 'to' => 'EDP_REVIEW', 'perm' => 'claim.review'],
            ['stage' => 'EDP_REVIEW', 'action' => 'FORWARD', 'from' => 'EDP_REVIEW', 'to' => 'SUPERVISOR_VERIFICATION', 'perm' => 'claim.review'],
            ['stage' => 'EDP_REVIEW', 'action' => 'REQUEST_REVISION', 'from' => 'EDP_REVIEW', 'to' => 'NEED_REVISION', 'perm' => 'claim.request_revision'],
            ['stage' => 'EDP_REVIEW', 'action' => 'REJECT', 'from' => 'EDP_REVIEW', 'to' => 'REJECTED', 'perm' => 'claim.reject'],

            ['stage' => 'SUPERVISOR_VERIFICATION', 'action' => 'VERIFY', 'from' => 'SUPERVISOR_VERIFICATION', 'to' => 'MANAGER_VERIFICATION', 'perm' => 'claim.verify'],
            ['stage' => 'SUPERVISOR_VERIFICATION', 'action' => 'REQUEST_REVISION', 'from' => 'SUPERVISOR_VERIFICATION', 'to' => 'NEED_REVISION', 'perm' => 'claim.request_revision'],
            ['stage' => 'SUPERVISOR_VERIFICATION', 'action' => 'REJECT', 'from' => 'SUPERVISOR_VERIFICATION', 'to' => 'REJECTED', 'perm' => 'claim.reject'],

            ['stage' => 'MANAGER_VERIFICATION', 'action' => 'VERIFY', 'from' => 'MANAGER_VERIFICATION', 'to' => 'FINANCE_VERIFICATION', 'perm' => 'claim.verify'],
            ['stage' => 'MANAGER_VERIFICATION', 'action' => 'REQUEST_REVISION', 'from' => 'MANAGER_VERIFICATION', 'to' => 'NEED_REVISION', 'perm' => 'claim.request_revision'],
            ['stage' => 'MANAGER_VERIFICATION', 'action' => 'REJECT', 'from' => 'MANAGER_VERIFICATION', 'to' => 'REJECTED', 'perm' => 'claim.reject'],

            ['stage' => 'FINANCE_VERIFICATION', 'action' => 'VERIFY', 'from' => 'FINANCE_VERIFICATION', 'to' => 'MANAGEMENT_APPROVAL', 'perm' => 'claim.finance_verify'],
            ['stage' => 'FINANCE_VERIFICATION', 'action' => 'REQUEST_REVISION', 'from' => 'FINANCE_VERIFICATION', 'to' => 'NEED_REVISION', 'perm' => 'claim.request_revision'],
            ['stage' => 'FINANCE_VERIFICATION', 'action' => 'REJECT', 'from' => 'FINANCE_VERIFICATION', 'to' => 'REJECTED', 'perm' => 'claim.reject'],

            ['stage' => 'MANAGEMENT_APPROVAL', 'action' => 'APPROVE', 'from' => 'MANAGEMENT_APPROVAL', 'to' => 'APPROVED', 'perm' => 'claim.approve'],
            ['stage' => 'MANAGEMENT_APPROVAL', 'action' => 'REQUEST_REVISION', 'from' => 'MANAGEMENT_APPROVAL', 'to' => 'NEED_REVISION', 'perm' => 'claim.request_revision'],
            ['stage' => 'MANAGEMENT_APPROVAL', 'action' => 'REJECT', 'from' => 'MANAGEMENT_APPROVAL', 'to' => 'REJECTED', 'perm' => 'claim.reject'],

            ['stage' => 'APPROVED', 'action' => 'PROCESS_PAYMENT', 'from' => 'APPROVED', 'to' => 'PAYMENT_PROCESS', 'perm' => 'claim.pay'],
            ['stage' => 'PAYMENT_PROCESS', 'action' => 'COMPLETE_PAYMENT', 'from' => 'PAYMENT_PROCESS', 'to' => 'PAID', 'perm' => 'claim.pay'],
            ['stage' => 'DRAFT', 'action' => 'CANCEL', 'from' => 'DRAFT', 'to' => 'CANCELLED', 'perm' => 'claim.update'],
        ];

        foreach ($transitions as $t) {
            WorkflowTransition::updateOrCreate(
                [
                    'workflow_stage_id' => $stages[$t['stage']]->id,
                    'action' => $t['action'],
                    'from_state' => $t['from'],
                    'to_state' => $t['to'],
                ],
                ['required_permission' => $t['perm']]
            );
        }

        // 8. APPROVAL POLICY (ANY_OF: DBM OR BM)
        $approvalPolicy = ApprovalPolicy::updateOrCreate(
            ['workflow_stage_id' => $stages['MANAGEMENT_APPROVAL']->id, 'name' => 'Management Approval (DBM or BM)'],
            [
                'version' => 1,
                'approval_rule' => ApprovalRule::ANY_OF,
                'minimum_approvals' => 1,
                'priority' => 10,
                'effective_from' => '2026-01-01',
                'is_active' => true,
            ]
        );
        $approvalPolicy->roles()->sync([$roles['DBM']->id, $roles['BM']->id]);

        // 9. SLA POLICIES
        foreach (['EDP_REVIEW', 'SUPERVISOR_VERIFICATION', 'MANAGER_VERIFICATION', 'FINANCE_VERIFICATION', 'MANAGEMENT_APPROVAL', 'PAYMENT_PROCESS'] as $stageCode) {
            SlaPolicy::updateOrCreate(
                ['workflow_stage_id' => $stages[$stageCode]->id],
                [
                    'name' => "SLA Stage {$stageCode}",
                    'duration_minutes' => 1440, // 24 hours
                    'calendar_type' => 'WORKING_DAYS',
                    'effective_from' => '2026-01-01',
                    'is_active' => true,
                ]
            );
        }

        // 10. SAMPLE DEMO CLAIMS
        $operasionalUser = $users['EDP_OPERASIONAL'];

        // Sample 1: DRAFT BBM Claim
        $claim1 = Claim::create([
            'claim_number' => 'CLM-202610-0001',
            'requester_id' => $operasionalUser->id,
            'claim_type' => ClaimType::BBM,
            'claim_date' => '2026-10-01',
            'description' => 'Reimbursement bensin supervisi toko area Timur',
            'total_amount' => 150000.00,
            'current_state' => ClaimState::DRAFT,
        ]);
        ClaimReceipt::create([
            'claim_id' => $claim1->id,
            'receipt_number' => 'SPBU-001239',
            'transaction_date' => '2026-10-01',
            'merchant' => 'SPBU Pertamina 34-12301',
            'amount' => 150000.00,
            'description' => 'Pertamax 11.62 Liter',
        ]);
        ClaimBbmDetail::create([
            'claim_id' => $claim1->id,
            'vehicle_id' => $vehicles[0]->id,
            'vehicle_type' => VehicleType::MOTOR,
            'distance_km' => 450.00,
            'fuel_liter' => 11.62,
            'actual_km_per_liter' => 38.7263,
            'standard_km_per_liter' => 40.00,
            'variance_km_per_liter' => -1.2737,
            'standard_snapshot_source' => 'Policy 2026',
        ]);

        // Sample 2: TOL Claim in Review
        $claim2 = Claim::create([
            'claim_number' => 'CLM-202610-0002',
            'requester_id' => $operasionalUser->id,
            'claim_type' => ClaimType::TOL,
            'claim_date' => '2026-10-02',
            'description' => 'Biaya e-Toll dan parkir maintenance server cabang',
            'total_amount' => 85000.00,
            'current_state' => ClaimState::EDP_REVIEW,
            'submitted_at' => now()->subDay(),
        ]);
        ClaimReceipt::create([
            'claim_id' => $claim2->id,
            'receipt_number' => 'TOL-98712',
            'transaction_date' => '2026-10-02',
            'merchant' => 'Gerbang Tol Cikarang Utama',
            'amount' => 65000.00,
            'description' => 'Tol PP Cikarang',
        ]);
        ClaimReceipt::create([
            'claim_id' => $claim2->id,
            'receipt_number' => 'PRK-3341',
            'transaction_date' => '2026-10-02',
            'merchant' => 'Secure Parking Plaza',
            'amount' => 20000.00,
            'description' => 'Parkir 4 jam',
        ]);
        ClaimStatusHistory::create([
            'claim_id' => $claim2->id,
            'from_state' => 'DRAFT',
            'to_state' => 'SUBMITTED',
            'action' => 'SUBMIT',
            'actor_id' => $operasionalUser->id,
            'created_at' => now()->subDay(),
        ]);
        ClaimStatusHistory::create([
            'claim_id' => $claim2->id,
            'from_state' => 'SUBMITTED',
            'to_state' => 'EDP_REVIEW',
            'action' => 'START_REVIEW',
            'actor_id' => $users['EDP_ADM']->id,
            'created_at' => now()->subHours(12),
        ]);

        // Sample 3: HEALTH Claim in Supervisor Verification
        $claim3 = Claim::create([
            'claim_number' => 'CLM-202610-0003',
            'requester_id' => $operasionalUser->id,
            'claim_type' => ClaimType::HEALTH,
            'claim_date' => '2026-10-03',
            'description' => 'Rawat jalan dokter umum dan tebus obat resep flu/batuk',
            'total_amount' => 450000.00,
            'current_state' => ClaimState::SUPERVISOR_VERIFICATION,
            'submitted_at' => now()->subDays(2),
        ]);
        ClaimReceipt::create([
            'claim_id' => $claim3->id,
            'receipt_number' => 'KDK-0098',
            'transaction_date' => '2026-10-03',
            'merchant' => 'Klinik Kimia Farma',
            'amount' => 450000.00,
            'description' => 'Konsultasi dokter + obat antibiotic & vitamin',
        ]);
        ClaimHealthDetail::create([
            'claim_id' => $claim3->id,
            'health_policy_id' => $healthPolicy->id,
            'policy_limit_snapshot' => 10000000.00,
            'claim_year' => 2026,
        ]);

        // Sample 4: BBM Claim in Management Approval (Ready for DBM or BM to test approval!)
        $claim4 = Claim::create([
            'claim_number' => 'CLM-202610-0004',
            'requester_id' => $operasionalUser->id,
            'claim_type' => ClaimType::BBM,
            'claim_date' => '2026-10-03',
            'description' => 'BBM Mobil operasional pengiriman hardware antar gudang',
            'total_amount' => 500000.00,
            'current_state' => ClaimState::MANAGEMENT_APPROVAL,
            'submitted_at' => now()->subDays(3),
        ]);
        ClaimReceipt::create([
            'claim_id' => $claim4->id,
            'receipt_number' => 'SPBU-554432',
            'transaction_date' => '2026-10-03',
            'merchant' => 'SPBU Pertamina 31-10022',
            'amount' => 500000.00,
            'description' => 'Pertamax 38.75 Liter',
        ]);
        ClaimBbmDetail::create([
            'claim_id' => $claim4->id,
            'vehicle_id' => $vehicles[2]->id, // Avanza
            'vehicle_type' => VehicleType::CAR,
            'distance_km' => 520.00,
            'fuel_liter' => 38.75,
            'actual_km_per_liter' => 13.4194,
            'standard_km_per_liter' => 13.00,
            'variance_km_per_liter' => 0.4194,
            'standard_snapshot_source' => 'Policy 2026',
        ]);
        WorkflowInstance::create([
            'claim_id' => $claim4->id,
            'workflow_definition_id' => $wfDef->id,
            'workflow_version' => 1,
            'started_at' => now()->subDays(3),
        ]);

        // Sample 5: PAID Claim with payment details
        $claim5 = Claim::create([
            'claim_number' => 'CLM-202609-0088',
            'requester_id' => $operasionalUser->id,
            'claim_type' => ClaimType::HEALTH,
            'claim_date' => '2026-09-15',
            'description' => 'Pemeriksaan laboratorium dan vitamin',
            'total_amount' => 1250000.00,
            'current_state' => ClaimState::PAID,
            'submitted_at' => now()->subDays(20),
            'approved_at' => now()->subDays(18),
            'paid_at' => now()->subDays(17),
        ]);
        ClaimReceipt::create([
            'claim_id' => $claim5->id,
            'receipt_number' => 'LAB-8812',
            'transaction_date' => '2026-09-15',
            'merchant' => 'Laboratorium Pramita',
            'amount' => 1250000.00,
            'description' => 'Pemeriksaan darah lengkap dan obat',
        ]);
        ClaimHealthDetail::create([
            'claim_id' => $claim5->id,
            'health_policy_id' => $healthPolicy->id,
            'policy_limit_snapshot' => 10000000.00,
            'claim_year' => 2026,
        ]);
        Payment::create([
            'claim_id' => $claim5->id,
            'processed_by' => $users['FINANCE']->id,
            'amount' => 1250000.00,
            'payment_method' => 'BANK_TRANSFER',
            'bank_name' => 'BCA',
            'bank_account_number' => '5432109876',
            'bank_account_name' => 'Budi Operasional',
            'payment_date' => now()->subDays(17),
            'transfer_reference' => 'TRX-BCA-20260918-001',
            'status' => PaymentStatus::PAID,
            'notes' => 'Pembayaran reimbursement via KlikBCA Bisnis',
        ]);
    }
}
