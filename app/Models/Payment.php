<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'claim_id',
        'processed_by',
        'amount',
        'payment_method',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'payment_date',
        'transfer_reference',
        'payment_proof_document_id',
        'status',
        'idempotency_key',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
        'status' => PaymentStatus::class,
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function proofDocument(): BelongsTo
    {
        return $this->belongsTo(ClaimDocument::class, 'payment_proof_document_id');
    }
}
