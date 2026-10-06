<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlaPolicy extends Model
{
    protected $fillable = [
        'workflow_stage_id',
        'name',
        'duration_minutes',
        'calendar_type',
        'effective_from',
        'effective_until',
        'is_active',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(WorkflowStage::class, 'workflow_stage_id');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(SlaInstance::class);
    }
}
