<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthPolicy extends Model
{
    protected $fillable = [
        'name',
        'annual_limit',
        'effective_from',
        'effective_until',
        'is_active',
    ];

    protected $casts = [
        'annual_limit' => 'decimal:2',
        'effective_from' => 'date',
        'effective_until' => 'date',
        'is_active' => 'boolean',
    ];

    public static function getActivePolicy(?string $date = null): ?self
    {
        $date = $date ?: now()->toDateString();

        return static::where('is_active', true)
            ->where('effective_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $date);
            })
            ->latest('effective_from')
            ->first();
    }
}
