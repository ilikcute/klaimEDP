<?php

namespace App\Http\Controllers;

use App\Enums\ClaimState;
use App\Models\Claim;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected ReportService $reportService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $kpi = $this->reportService->getDashboardKPI($user);

        // Recent claims
        $recentClaims = Claim::with(['requester', 'bbmDetail', 'payment'])
            ->forUser($user)
            ->latest('id')
            ->limit(5)
            ->get();

        // Pending tasks (requires action by current user)
        $tasksQuery = Claim::with(['requester', 'bbmDetail'])
            ->latest('id');

        $userRoles = $user->roles->pluck('name')->toArray();

        if ($user->hasRole('ADMIN')) {
            $tasksQuery->whereNotIn('current_state', [ClaimState::PAID, ClaimState::REJECTED, ClaimState::CANCELLED]);
        } elseif (in_array('EDP_ADM', $userRoles)) {
            $tasksQuery->whereIn('current_state', [ClaimState::SUBMITTED, ClaimState::EDP_REVIEW]);
        } elseif (in_array('EDP_SUPERVISOR', $userRoles)) {
            $tasksQuery->where('current_state', ClaimState::SUPERVISOR_VERIFICATION);
        } elseif (in_array('EDP_MANAGER', $userRoles)) {
            $tasksQuery->where('current_state', ClaimState::MANAGER_VERIFICATION);
        } elseif (in_array('FINANCE', $userRoles)) {
            $tasksQuery->whereIn('current_state', [ClaimState::FINANCE_VERIFICATION, ClaimState::APPROVED, ClaimState::PAYMENT_PROCESS]);
        } elseif (in_array('DBM', $userRoles) || in_array('BM', $userRoles)) {
            $tasksQuery->where('current_state', ClaimState::MANAGEMENT_APPROVAL);
        } else {
            // Requester: claims that need revision
            $tasksQuery->where('requester_id', $user->id)
                ->where('current_state', ClaimState::NEED_REVISION);
        }

        $pendingTasks = $tasksQuery->limit(10)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'kpi' => $kpi,
                'recent_claims' => $recentClaims,
                'pending_tasks' => $pendingTasks,
            ],
        ]);
    }
}
