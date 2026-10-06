<?php

namespace App\Models;

use App\Enums\ApprovalRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalPolicy extends Model
{
    protected $fillable = [
        'workflow_stage_id',
        'name',
        'version',
        'approval_rule',
        'minimum_approvals',
        'priority',
        'min_amount',
        'max_amount',
        'claim_type',
        'effective_from',
        'effective_until',
        'is_active',
    ];

    protected $casts = [
        'approval_rule' => ApprovalRule::class,
        'minimum_approvals' => 'integer',
        'priority' => 'integer',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(WorkflowStage::class, 'workflow_stage_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'approval_policy_roles');
    }

    public function scopes(): HasMany
    {
        return $this->hasMany(ApprovalPolicyScope::class);
    }
}
