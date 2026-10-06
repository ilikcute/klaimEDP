<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimHealthDetail extends Model
{
    protected $fillable = [
        'claim_id',
        'health_policy_id',
        'policy_limit_snapshot',
        'claim_year',
    ];

    protected $casts = [
        'policy_limit_snapshot' => 'decimal:2',
        'claim_year' => 'integer',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(HealthPolicy::class, 'health_policy_id');
    }
}
