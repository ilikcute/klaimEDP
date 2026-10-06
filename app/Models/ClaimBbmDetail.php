<?php

namespace App\Models;

use App\Enums\VehicleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimBbmDetail extends Model
{
    protected $fillable = [
        'claim_id',
        'vehicle_id',
        'vehicle_type',
        'last_odometer_km',
        'current_odometer_km',
        'distance_km',
        'fuel_liter',
        'actual_km_per_liter',
        'standard_km_per_liter',
        'variance_km_per_liter',
        'standard_snapshot_source',
    ];

    protected $casts = [
        'vehicle_type' => VehicleType::class,
        'last_odometer_km' => 'decimal:2',
        'current_odometer_km' => 'decimal:2',
        'distance_km' => 'decimal:2',
        'fuel_liter' => 'decimal:2',
        'actual_km_per_liter' => 'decimal:4',
        'standard_km_per_liter' => 'decimal:2',
        'variance_km_per_liter' => 'decimal:4',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getComplianceStatusAttribute(): string
    {
        if ($this->variance_km_per_liter >= 0) {
            return 'COMPLIANT';
        }
        // If variance is negative by more than 10%, below standard
        if ($this->standard_km_per_liter > 0 && ($this->variance_km_per_liter / $this->standard_km_per_liter) < -0.1) {
            return 'BELOW_STANDARD';
        }
        return 'COMPLIANT';
    }
}
