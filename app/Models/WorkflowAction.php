<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowAction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'claim_id',
        'workflow_instance_id',
        'transition_id',
        'from_state',
        'action',
        'to_state',
        'actor_id',
        'actor_role',
        'reason',
        'notes',
        'approval_policy_id',
        'approval_policy_version',
        'created_at',
    ];

    protected $casts = [
        'approval_policy_version' => 'integer',
        'created_at' => 'datetime',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    public function transition(): BelongsTo
    {
        return $this->belongsTo(WorkflowTransition::class, 'transition_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function approvalPolicy(): BelongsTo
    {
        return $this->belongsTo(ApprovalPolicy::class, 'approval_policy_id');
    }
}
