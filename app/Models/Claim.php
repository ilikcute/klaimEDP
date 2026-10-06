<?php

namespace App\Models;

use App\Enums\ClaimState;
use App\Enums\ClaimType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Claim extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'claim_number',
        'requester_id',
        'claim_type',
        'claim_date',
        'description',
        'total_amount',
        'current_state',
        'submitted_at',
        'approved_at',
        'paid_at',
    ];

    protected $casts = [
        'claim_type' => ClaimType::class,
        'claim_date' => 'date',
        'total_amount' => 'decimal:2',
        'current_state' => ClaimState::class,
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public static function generateClaimNumber(): string
    {
        $prefix = 'CLM-' . date('Ym') . '-';
        $latest = static::withTrashed()
            ->where('claim_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest->claim_number, $matches)) {
            $sequence = intval($matches[1]) + 1;
        }

        return $prefix . str_pad((string)$sequence, 4, '0', STR_PAD_LEFT);
    }

    public function recalculateTotal(): self
    {
        $this->total_amount = $this->receipts()->sum('amount');
        $this->save();
        return $this;
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(ClaimReceipt::class)->orderBy('transaction_date');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ClaimDocument::class)->latest();
    }

    public function bbmDetail(): HasOne
    {
        return $this->hasOne(ClaimBbmDetail::class);
    }

    public function healthDetail(): HasOne
    {
        return $this->hasOne(ClaimHealthDetail::class);
    }

    public function workflowInstance(): HasOne
    {
        return $this->hasOne(WorkflowInstance::class);
    }

    public function workflowActions(): HasMany
    {
        return $this->hasMany(WorkflowAction::class)->latest();
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ClaimStatusHistory::class)->latest();
    }

    public function slaInstances(): HasMany
    {
        return $this->hasMany(SlaInstance::class)->latest();
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->hasRole('ADMIN')) {
            return $query;
        }

        if ($user->hasRole('EDP_OPERASIONAL') && !$user->hasRole(['EDP_ADM', 'EDP_SUPERVISOR', 'EDP_MANAGER', 'FINANCE', 'DBM', 'BM'])) {
            return $query->where('requester_id', $user->id);
        }

        return $query;
    }
}
