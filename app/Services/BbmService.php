<?php

namespace App\Services;

use App\Models\BbmStandard;
use App\Models\Claim;
use App\Models\ClaimBbmDetail;
use App\Models\Vehicle;
use InvalidArgumentException;

class BbmService
{
    public function calculateAndAttach(
        Claim $claim,
        int $vehicleId,
        float $distanceKm,
        float $fuelLiter,
        ?float $lastOdometerKm = null,
        ?float $currentOdometerKm = null
    ): ClaimBbmDetail {
        if ($fuelLiter <= 0) {
            throw new InvalidArgumentException('Jumlah liter BBM harus lebih besar dari 0.');
        }

        if ($distanceKm <= 0) {
            throw new InvalidArgumentException('Jarak tempuh (KM) harus lebih besar dari 0.');
        }

        $vehicle = Vehicle::findOrFail($vehicleId);
        $claimDate = $claim->claim_date ? $claim->claim_date->toDateString() : now()->toDateString();
        $standard = BbmStandard::getActiveStandard($vehicle->vehicle_type, $claimDate);

        $standardKmPerLiter = $standard ? (float)$standard->standard_km_per_liter : ($vehicle->vehicle_type->value === 'MOTOR' ? 40.00 : 13.00);
        $actualKmPerLiter = round($distanceKm / $fuelLiter, 4);
        $varianceKmPerLiter = round($actualKmPerLiter - $standardKmPerLiter, 4);

        return ClaimBbmDetail::updateOrCreate(
            ['claim_id' => $claim->id],
            [
                'vehicle_id' => $vehicle->id,
                'vehicle_type' => $vehicle->vehicle_type,
                'last_odometer_km' => $lastOdometerKm,
                'current_odometer_km' => $currentOdometerKm,
                'distance_km' => $distanceKm,
                'fuel_liter' => $fuelLiter,
                'actual_km_per_liter' => $actualKmPerLiter,
                'standard_km_per_liter' => $standardKmPerLiter,
                'variance_km_per_liter' => $varianceKmPerLiter,
                'standard_snapshot_source' => $standard ? "Policy #{$standard->id}" : 'Default Fallback',
            ]
        );
    }
}
