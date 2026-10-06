<?php

namespace App\Http\Controllers;

use App\Models\BbmStandard;
use App\Models\Claim;
use App\Models\ClaimBbmDetail;
use App\Models\Vehicle;
use App\Services\BbmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BbmController extends Controller
{
    public function __construct(protected BbmService $bbmService) {}

    public function getStandards(): JsonResponse
    {
        $standards = BbmStandard::latest('effective_from')->get();

        return response()->json([
            'success' => true,
            'data' => $standards,
        ]);
    }

    public function storeStandard(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole('ADMIN')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'vehicle_type' => 'required|in:MOTOR,CAR',
            'standard_km_per_liter' => 'required|numeric|min:1',
            'effective_from' => 'required|date',
            'effective_until' => 'nullable|date|after:effective_from',
            'is_active' => 'boolean',
        ]);

        $standard = BbmStandard::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Standar BBM berhasil disimpan.',
            'data' => $standard,
        ], 201);
    }

    public function getVehicles(): JsonResponse
    {
        $vehicles = Vehicle::with('assignedUser:id,name,email')
            ->where('is_active', true)
            ->get()
            ->map(function ($vehicle) {
                $lastBbm = ClaimBbmDetail::where('vehicle_id', $vehicle->id)
                    ->whereNotNull('current_odometer_km')
                    ->latest('id')
                    ->first();

                $vehicle->last_odometer_km = $lastBbm ? (float)$lastBbm->current_odometer_km : null;
                return $vehicle;
            });

        return response()->json([
            'success' => true,
            'data' => $vehicles,
        ]);
    }

    public function getClaimBbm(Claim $claim): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $claim->bbmDetail()->with('vehicle')->first(),
        ]);
    }

    public function updateClaimBbm(Request $request, Claim $claim): JsonResponse
    {
        if (!$claim->current_state->isEditable() && !$request->user()->hasRole('ADMIN')) {
            return response()->json([
                'success' => false,
                'message' => 'Detail BBM tidak dapat diubah pada status saat ini.',
            ], 422);
        }

        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'distance_km' => 'required|numeric|min:0.1',
            'fuel_liter' => 'required|numeric|min:0.01',
            'last_odometer_km' => 'nullable|numeric|min:0',
            'current_odometer_km' => 'nullable|numeric|min:0',
        ]);

        $detail = $this->bbmService->calculateAndAttach(
            $claim,
            (int)$validated['vehicle_id'],
            (float)$validated['distance_km'],
            (float)$validated['fuel_liter'],
            isset($validated['last_odometer_km']) ? (float)$validated['last_odometer_km'] : null,
            isset($validated['current_odometer_km']) ? (float)$validated['current_odometer_km'] : null
        );

        return response()->json([
            'success' => true,
            'message' => 'Detail BBM berhasil diperbarui.',
            'data' => $detail->load('vehicle'),
        ]);
    }
}
