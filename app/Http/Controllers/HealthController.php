<?php

namespace App\Http\Controllers;

use App\Services\HealthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    public function __construct(protected HealthService $healthService) {}

    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $targetUserId = $request->filled('user_id') && $user->hasRole(['ADMIN', 'EDP_MANAGER', 'FINANCE', 'DBM', 'BM'])
            ? (int)$request->user_id
            : $user->id;

        $year = $request->filled('year') ? (int)$request->year : null;
        $summary = $this->healthService->getUtilizationSummary($targetUserId, $year);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}
