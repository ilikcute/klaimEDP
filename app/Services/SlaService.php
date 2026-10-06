<?php

namespace App\Services;

use App\Enums\SlaStatus;
use App\Models\Claim;
use App\Models\SlaInstance;
use App\Models\SlaPolicy;
use App\Models\WorkflowStage;

class SlaService
{
    public function startStageSla(Claim $claim, WorkflowStage $stage): SlaInstance
    {
        // Complete any open SLA instance for this claim
        $this->completeOpenSlas($claim);

        $policy = SlaPolicy::where('workflow_stage_id', $stage->id)
            ->where('is_active', true)
            ->latest('effective_from')
            ->first();

        $durationMinutes = $policy ? $policy->duration_minutes : 1440; // Default 24 hours
        $startedAt = now();
        $dueAt = (clone $startedAt)->addMinutes($durationMinutes);

        return SlaInstance::create([
            'claim_id' => $claim->id,
            'workflow_stage_id' => $stage->id,
            'sla_policy_id' => $policy?->id,
            'started_at' => $startedAt,
            'due_at' => $dueAt,
            'status' => SlaStatus::ON_TIME,
        ]);
    }

    public function completeOpenSlas(Claim $claim): void
    {
        $openInstances = SlaInstance::where('claim_id', $claim->id)
            ->whereNull('completed_at')
            ->get();

        $now = now();
        foreach ($openInstances as $instance) {
            $duration = $instance->started_at->diffInMinutes($now);
            $instance->completed_at = $now;
            $instance->duration_minutes = $duration;
            $instance->status = $now->isAfter($instance->due_at) ? SlaStatus::BREACHED : SlaStatus::ON_TIME;
            $instance->save();
        }
    }
}
