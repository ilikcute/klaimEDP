<?php

namespace App\Models;

use App\Enums\VehicleType;
use Illuminate\Database\Eloquent\Model;

class BbmStandard extends Model
{
    protected $fillable = [
        'vehicle_type',
        'standard_km_per_liter',
        'effective_from',
        'effective_until',
        'is_active',
    ];

    protected $casts = [
        'vehicle_type' => VehicleType::class,
        'standard_km_per_liter' => 'decimal:2',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
    ];

    public static function getActiveStandard(VehicleType|string $vehicleType, ?string $date = null): ?self
    {
        $type = $vehicleType instanceof VehicleType ? $vehicleType->value : $vehicleType;
        $date = $date ?: now()->toDateString();

        return static::where('vehicle_type', $type)
            ->where('is_active', true)
            ->where('effective_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $date);
            })
            ->latest('effective_from')
            ->first();
    }
}
