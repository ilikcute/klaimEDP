<?php

namespace App\Models;

use App\Enums\SlaStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlaInstance extends Model
{
    protected $fillable = [
        'claim_id',
        'workflow_stage_id',
        'sla_policy_id',
        'started_at',
        'due_at',
        'completed_at',
        'status',
        'duration_minutes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'status' => SlaStatus::class,
        'duration_minutes' => 'integer',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(WorkflowStage::class, 'workflow_stage_id');
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class, 'sla_policy_id');
    }

    public function calculateStatus(): SlaStatus
    {
        if ($this->completed_at) {
            return $this->completed_at->isAfter($this->due_at) ? SlaStatus::BREACHED : SlaStatus::ON_TIME;
        }

        $now = now();
        if ($now->isAfter($this->due_at)) {
            return SlaStatus::BREACHED;
        }

        // If less than 20% time remaining, it's at risk
        $totalMinutes = $this->started_at->diffInMinutes($this->due_at);
        $remainingMinutes = $now->diffInMinutes($this->due_at);

        if ($totalMinutes > 0 && ($remainingMinutes / $totalMinutes) <= 0.25) {
            return SlaStatus::AT_RISK;
        }

        return SlaStatus::ON_TIME;
    }
}
