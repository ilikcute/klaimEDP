<?php

namespace App\Services;

use App\Enums\ClaimState;
use App\Enums\ClaimType;
use App\Models\Claim;
use App\Models\ClaimHealthDetail;
use App\Models\HealthPolicy;

class HealthService
{
    public function getUtilizationSummary(int $userId, ?int $year = null): array
    {
        $year = $year ?: (int)date('Y');
        $policy = HealthPolicy::getActivePolicy("{$year}-01-01");
        $limit = $policy ? (float)$policy->annual_limit : 10000000.00;

        $claims = Claim::where('requester_id', $userId)
            ->where('claim_type', ClaimType::HEALTH)
            ->whereYear('claim_date', $year)
            ->whereNotIn('current_state', [ClaimState::REJECTED, ClaimState::CANCELLED])
            ->get();

        $paid = 0.0;
        $approved = 0.0;
        $pending = 0.0;

        foreach ($claims as $claim) {
            $amount = (float)$claim->total_amount;
            if ($claim->current_state === ClaimState::PAID) {
                $paid += $amount;
            } elseif ($claim->current_state === ClaimState::APPROVED || $claim->current_state === ClaimState::PAYMENT_PROCESS) {
                $approved += $amount;
            } else {
                $pending += $amount;
            }
        }

        $used = $paid + $approved;
        $remaining = max(0.0, $limit - $used);
        $utilizationPercent = $limit > 0 ? round(($used / $limit) * 100, 2) : 0;

        return [
            'year' => $year,
            'limit' => $limit,
            'paid' => $paid,
            'approved' => $approved,
            'pending' => $pending,
            'remaining' => $remaining,
            'utilization_percent' => $utilizationPercent,
        ];
    }

    public function attachHealthDetail(Claim $claim, ?int $year = null): ClaimHealthDetail
    {
        $claimYear = $year ?: (int)($claim->claim_date ? $claim->claim_date->format('Y') : date('Y'));
        $policy = HealthPolicy::getActivePolicy("{$claimYear}-01-01");

        $limit = $policy ? $policy->annual_limit : 10000000.00;

        return ClaimHealthDetail::updateOrCreate(
            ['claim_id' => $claim->id],
            [
                'health_policy_id' => $policy?->id,
                'policy_limit_snapshot' => $limit,
                'claim_year' => $claimYear,
            ]
        );
    }
}
