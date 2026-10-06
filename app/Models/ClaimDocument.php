<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimDocument extends Model
{
    protected $fillable = [
        'claim_id',
        'claim_receipt_id',
        'document_type',
        'status',
        'original_filename',
        'storage_disk',
        'storage_path',
        'mime_type',
        'file_size',
        'checksum_sha256',
        'latitude',
        'longitude',
        'gps_accuracy_meters',
        'captured_at',
        'location_address',
        'uploaded_by',
        'received_at',
        'verified_at',
    ];

    protected $casts = [
        'document_type' => DocumentType::class,
        'status' => DocumentStatus::class,
        'file_size' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'gps_accuracy_meters' => 'float',
        'captured_at' => 'datetime',
        'received_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(ClaimReceipt::class, 'claim_receipt_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
