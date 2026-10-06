<?php

namespace App\Models;

use App\Enums\VehicleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'vehicle_type',
        'plate_number',
        'assigned_user_id',
        'is_active',
    ];

    protected $casts = [
        'vehicle_type' => VehicleType::class,
        'is_active' => 'boolean',
        'assigned_user_id' => 'integer',
    ];

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
