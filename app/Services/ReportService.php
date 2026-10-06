<?php

namespace App\Services;

use App\Enums\ClaimState;
use App\Enums\ClaimType;
use App\Enums\SlaStatus;
use App\Models\Claim;
use App\Models\ClaimBbmDetail;
use App\Models\ClaimReceipt;
use App\Models\HealthPolicy;
use App\Models\Payment;
use App\Models\SlaInstance;
use App\Models\User;
use App\Models\WorkflowAction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getDashboardKPI(User $user): array
    {
        $query = Claim::query()->forUser($user);

        $totalClaims = (clone $query)->count();
        $totalAmount = (float)(clone $query)->sum('total_amount');
        
        $paidAmount = (float)Payment::where('status', 'PAID')
            ->when(!$user->hasRole(['ADMIN', 'FINANCE', 'EDP_MANAGER', 'DBM', 'BM']), function ($q) use ($user) {
                $q->whereHas('claim', fn($c) => $c->where('requester_id', $user->id));
            })
            ->sum('amount');

        $pendingClaims = (clone $query)->whereNotIn('current_state', [
            ClaimState::PAID, ClaimState::REJECTED, ClaimState::CANCELLED, ClaimState::DRAFT
        ])->count();

        $pendingApproval = (clone $query)->where('current_state', ClaimState::MANAGEMENT_APPROVAL)->count();
        $pendingVerification = (clone $query)->whereIn('current_state', [
            ClaimState::SUPERVISOR_VERIFICATION, ClaimState::MANAGER_VERIFICATION, ClaimState::FINANCE_VERIFICATION
        ])->count();

        $slaAtRisk = SlaInstance::where('status', SlaStatus::AT_RISK)
            ->whereNull('completed_at')
            ->count();

        $slaBreached = SlaInstance::where('status', SlaStatus::BREACHED)->count();

        // Calculate average processing days for paid claims
        $paidClaims = Claim::where('current_state', ClaimState::PAID)
            ->whereNotNull('submitted_at')
            ->whereNotNull('paid_at')
            ->get();

        $avgDays = 0;
        if ($paidClaims->count() > 0) {
            $totalDays = $paidClaims->reduce(function ($carry, $item) {
                return $carry + $item->submitted_at->diffInDays($item->paid_at);
            }, 0);
            $avgDays = round($totalDays / $paidClaims->count(), 1);
        }

        return [
            'total_claims' => $totalClaims,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'pending_claims' => $pendingClaims,
            'pending_approval' => $pendingApproval,
            'pending_verification' => $pendingVerification,
            'sla_at_risk' => $slaAtRisk,
            'sla_breached' => $slaBreached,
            'average_processing_days' => $avgDays,
        ];
    }

    public function getClaimSummaryReport(array $filters = []): array
    {
        $query = Claim::with(['requester', 'receipts', 'payment'])
            ->latest('claim_date');

        if (!empty($filters['claim_type'])) {
            $query->where('claim_type', $filters['claim_type']);
        }
        if (!empty($filters['status'])) {
            $query->where('current_state', $filters['status']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('claim_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('claim_date', '<=', $filters['date_to']);
        }
        if (!empty($filters['requester_id'])) {
            $query->where('requester_id', $filters['requester_id']);
        }

        $claims = $query->paginate($filters['per_page'] ?? 25);

        return [
            'data' => $claims->items(),
            'pagination' => [
                'current_page' => $claims->currentPage(),
                'last_page' => $claims->lastPage(),
                'per_page' => $claims->perPage(),
                'total' => $claims->total(),
            ]
        ];
    }

    public function getClaimByTypeReport(): array
    {
        $types = [ClaimType::BBM, ClaimType::TOL, ClaimType::HEALTH];
        $totalSystemAmount = (float)Claim::whereNotIn('current_state', [ClaimState::REJECTED, ClaimState::CANCELLED])->sum('total_amount');

        $result = [];
        foreach ($types as $type) {
            $claims = Claim::where('claim_type', $type->value)
                ->whereNotIn('current_state', [ClaimState::REJECTED, ClaimState::CANCELLED]);

            $count = $claims->count();
            $sum = (float)$claims->sum('total_amount');
            $avg = $count > 0 ? round($sum / $count, 2) : 0;
            $percentage = $totalSystemAmount > 0 ? round(($sum / $totalSystemAmount) * 100, 2) : 0;

            $result[] = [
                'type' => $type->value,
                'label' => $type->label(),
                'count' => $count,
                'total_amount' => $sum,
                'average_amount' => $avg,
                'percentage' => $percentage,
            ];
        }

        return $result;
    }

    public function getHistoricalTrendReport(int $months = 6): array
    {
        $trend = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;
            $label = $date->translatedFormat('M Y');

            $claims = Claim::whereYear('claim_date', $year)
                ->whereMonth('claim_date', $month);

            $count = (clone $claims)->count();
            $claimedAmount = (float)(clone $claims)->sum('total_amount');
            
            $paidAmount = (float)Payment::whereYear('payment_date', $year)
                ->whereMonth('payment_date', $month)
                ->where('status', 'PAID')
                ->sum('amount');

            $avgClaim = $count > 0 ? round($claimedAmount / $count, 2) : 0;

            $trend[] = [
                'period' => $label,
                'year' => $year,
                'month' => $month,
                'claim_count' => $count,
                'claimed_amount' => $claimedAmount,
                'paid_amount' => $paidAmount,
                'average_claim' => $avgClaim,
            ];
        }

        return $trend;
    }

    public function getSlaReport(): array
    {
        $totalInstances = SlaInstance::count();
        $onTime = SlaInstance::where('status', SlaStatus::ON_TIME)->count();
        $atRisk = SlaInstance::where('status', SlaStatus::AT_RISK)->count();
        $breached = SlaInstance::where('status', SlaStatus::BREACHED)->count();

        $durations = SlaInstance::whereNotNull('duration_minutes')
            ->orderBy('duration_minutes')
            ->pluck('duration_minutes')
            ->toArray();

        $avgMinutes = count($durations) > 0 ? array_sum($durations) / count($durations) : 0;
        $median = $this->calculatePercentile($durations, 50);
        $p90 = $this->calculatePercentile($durations, 90);

        // Stage breakdown
        $stageBreakdown = DB::table('sla_instances')
            ->join('workflow_stages', 'sla_instances.workflow_stage_id', '=', 'workflow_stages.id')
            ->select(
                'workflow_stages.name as stage_name',
                'workflow_stages.code as stage_code',
                DB::raw('COUNT(*) as total'),
                DB::raw('AVG(duration_minutes) as avg_duration'),
                DB::raw('SUM(CASE WHEN sla_instances.status = "BREACHED" THEN 1 ELSE 0 END) as breached_count')
            )
            ->groupBy('workflow_stages.id', 'workflow_stages.name', 'workflow_stages.code')
            ->get();

        return [
            'metrics' => [
                'total_tasks' => $totalInstances,
                'on_time_percent' => $totalInstances > 0 ? round(($onTime / $totalInstances) * 100, 1) : 0,
                'at_risk_percent' => $totalInstances > 0 ? round(($atRisk / $totalInstances) * 100, 1) : 0,
                'breached_percent' => $totalInstances > 0 ? round(($breached / $totalInstances) * 100, 1) : 0,
                'average_duration_hours' => round($avgMinutes / 60, 1),
                'median_duration_hours' => round($median / 60, 1),
                'p90_duration_hours' => round($p90 / 60, 1),
            ],
            'stage_breakdown' => $stageBreakdown,
        ];
    }

    public function getManagementApprovalReport(): array
    {
        $dbmApprovals = WorkflowAction::where('from_state', ClaimState::MANAGEMENT_APPROVAL->value)
            ->where('action', 'APPROVE')
            ->where('actor_role', 'DBM')
            ->count();

        $bmApprovals = WorkflowAction::where('from_state', ClaimState::MANAGEMENT_APPROVAL->value)
            ->where('action', 'APPROVE')
            ->where('actor_role', 'BM')
            ->count();

        $dbmAmount = (float)Claim::whereHas('workflowActions', function ($q) {
            $q->where('from_state', ClaimState::MANAGEMENT_APPROVAL->value)
              ->where('action', 'APPROVE')
              ->where('actor_role', 'DBM');
        })->sum('total_amount');

        $bmAmount = (float)Claim::whereHas('workflowActions', function ($q) {
            $q->where('from_state', ClaimState::MANAGEMENT_APPROVAL->value)
              ->where('action', 'APPROVE')
              ->where('actor_role', 'BM');
        })->sum('total_amount');

        return [
            'dbm' => [
                'role' => 'DBM (Deputy Branch Manager)',
                'approved_count' => $dbmApprovals,
                'total_amount' => $dbmAmount,
            ],
            'bm' => [
                'role' => 'BM (Branch Manager)',
                'approved_count' => $bmApprovals,
                'total_amount' => $bmAmount,
            ],
            'rule' => 'ANY_OF (DBM OR BM)',
        ];
    }

    public function getBbmReport(): array
    {
        $bbmDetails = ClaimBbmDetail::with(['claim.requester', 'vehicle'])->get();

        $totalKm = $bbmDetails->sum('distance_km');
        $totalLiter = $bbmDetails->sum('fuel_liter');
        $avgKmLiter = $totalLiter > 0 ? round($totalKm / $totalLiter, 2) : 0;

        $compliantCount = $bbmDetails->filter(fn($b) => $b->variance_km_per_liter >= 0)->count();
        $belowCount = $bbmDetails->filter(fn($b) => $b->variance_km_per_liter < 0)->count();
        $total = $bbmDetails->count();

        return [
            'summary' => [
                'total_km' => round($totalKm, 2),
                'total_liter' => round($totalLiter, 2),
                'average_km_per_liter' => $avgKmLiter,
                'compliant_percent' => $total > 0 ? round(($compliantCount / $total) * 100, 1) : 0,
                'below_standard_percent' => $total > 0 ? round(($belowCount / $total) * 100, 1) : 0,
            ],
            'details' => $bbmDetails->map(fn($item) => [
                'claim_number' => $item->claim?->claim_number,
                'requester' => $item->claim?->requester?->name,
                'vehicle' => $item->vehicle?->name . ' (' . $item->vehicle?->plate_number . ')',
                'vehicle_type' => $item->vehicle_type->value,
                'distance_km' => $item->distance_km,
                'fuel_liter' => $item->fuel_liter,
                'actual_km_per_liter' => $item->actual_km_per_liter,
                'standard_km_per_liter' => $item->standard_km_per_liter,
                'variance_km_per_liter' => $item->variance_km_per_liter,
                'compliance_status' => $item->compliance_status,
            ]),
        ];
    }

    public function getHealthReport(?int $year = null): array
    {
        $year = $year ?: (int)date('Y');
        $policy = HealthPolicy::getActivePolicy("{$year}-01-01");
        $limit = $policy ? (float)$policy->annual_limit : 10000000.00;

        $users = User::where('is_active', true)->get();
        $healthService = app(HealthService::class);

        $results = [];
        foreach ($users as $user) {
            $summary = $healthService->getUtilizationSummary($user->id, $year);
            if ($summary['paid'] > 0 || $summary['pending'] > 0 || $summary['approved'] > 0) {
                $results[] = [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'annual_limit' => $limit,
                    'paid' => $summary['paid'],
                    'approved' => $summary['approved'],
                    'pending' => $summary['pending'],
                    'remaining' => $summary['remaining'],
                    'utilization_percent' => $summary['utilization_percent'],
                ];
            }
        }

        return [
            'year' => $year,
            'annual_limit' => $limit,
            'users' => $results,
        ];
    }

    public function getAgingReport(): array
    {
        $openClaims = Claim::whereNotIn('current_state', [
            ClaimState::PAID, ClaimState::REJECTED, ClaimState::CANCELLED, ClaimState::DRAFT
        ])->get();

        $brackets = [
            '0_1_day' => 0,
            '2_3_days' => 0,
            '4_7_days' => 0,
            '8_14_days' => 0,
            'over_14_days' => 0,
        ];

        $now = now();
        foreach ($openClaims as $claim) {
            $days = $claim->submitted_at ? $claim->submitted_at->diffInDays($now) : $claim->created_at->diffInDays($now);
            if ($days <= 1) {
                $brackets['0_1_day']++;
            } elseif ($days <= 3) {
                $brackets['2_3_days']++;
            } elseif ($days <= 7) {
                $brackets['4_7_days']++;
            } elseif ($days <= 14) {
                $brackets['8_14_days']++;
            } else {
                $brackets['over_14_days']++;
            }
        }

        return [
            'brackets' => $brackets,
            'total_open' => $openClaims->count(),
        ];
    }

    public function getDuplicateDetectionReport(): array
    {
        // Detect receipts with identical amount and transaction_date across claims
        $duplicates = DB::table('claim_receipts as r1')
            ->join('claim_receipts as r2', function ($join) {
                $join->on('r1.amount', '=', 'r2.amount')
                    ->on('r1.transaction_date', '=', 'r2.transaction_date')
                    ->on('r1.id', '<', 'r2.id');
            })
            ->join('claims as c1', 'r1.claim_id', '=', 'c1.id')
            ->join('claims as c2', 'r2.claim_id', '=', 'c2.id')
            ->join('users as u1', 'c1.requester_id', '=', 'u1.id')
            ->select(
                'r1.amount',
                'r1.transaction_date',
                'r1.merchant',
                'c1.claim_number as claim1_number',
                'c2.claim_number as claim2_number',
                'u1.name as requester_name'
            )
            ->limit(50)
            ->get();

        return [
            'potential_duplicates_count' => $duplicates->count(),
            'records' => $duplicates,
        ];
    }

    protected function calculatePercentile(array $data, int $percentile): float
    {
        if (empty($data)) return 0.0;
        sort($data);
        $index = ($percentile / 100) * (count($data) - 1);
        $fraction = $index - floor($index);
        $lower = $data[(int)floor($index)];
        $upper = $data[(int)ceil($index)];

        return $lower + ($upper - $lower) * $fraction;
    }
}
