<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Services\WorkflowEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function __construct(protected WorkflowEngine $workflowEngine) {}

    public function submit(Request $request, Claim $claim): JsonResponse
    {
        $action = $claim->current_state->value === 'NEED_REVISION' ? 'RESUBMIT' : 'SUBMIT';
        $updated = $this->workflowEngine->transition(
            $claim,
            $action,
            $request->user(),
            ['notes' => $request->notes]
        );

        return response()->json([
            'success' => true,
            'message' => 'Klaim berhasil diajukan.',
            'data' => $updated,
        ]);
    }

    public function review(Request $request, Claim $claim): JsonResponse
    {
        $request->validate([
            'decision' => 'required|in:START_REVIEW,FORWARD,REQUEST_REVISION,REJECT',
            'notes' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $updated = $this->workflowEngine->transition(
            $claim,
            $request->decision,
            $request->user(),
            $request->only(['notes', 'reason', 'reason_code'])
        );

        return response()->json([
            'success' => true,
            'message' => 'Review EDP Adm berhasil diproses.',
            'data' => $updated,
        ]);
    }

    public function supervisorVerification(Request $request, Claim $claim): JsonResponse
    {
        $request->validate([
            'decision' => 'required|in:VERIFY,REQUEST_REVISION,REJECT',
            'notes' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $updated = $this->workflowEngine->transition(
            $claim,
            $request->decision,
            $request->user(),
            $request->only(['notes', 'reason', 'reason_code'])
        );

        return response()->json([
            'success' => true,
            'message' => 'Verifikasi Supervisor berhasil diproses.',
            'data' => $updated,
        ]);
    }

    public function managerVerification(Request $request, Claim $claim): JsonResponse
    {
        $request->validate([
            'decision' => 'required|in:VERIFY,REQUEST_REVISION,REJECT',
            'notes' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $updated = $this->workflowEngine->transition(
            $claim,
            $request->decision,
            $request->user(),
            $request->only(['notes', 'reason', 'reason_code'])
        );

        return response()->json([
            'success' => true,
            'message' => 'Verifikasi Manager berhasil diproses.',
            'data' => $updated,
        ]);
    }

    public function financeVerification(Request $request, Claim $claim): JsonResponse
    {
        $request->validate([
            'decision' => 'required|in:VERIFY,REQUEST_REVISION,REJECT',
            'notes' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $updated = $this->workflowEngine->transition(
            $claim,
            $request->decision,
            $request->user(),
            $request->only(['notes', 'reason', 'reason_code'])
        );

        return response()->json([
            'success' => true,
            'message' => 'Verifikasi Finance berhasil diproses.',
            'data' => $updated,
        ]);
    }

    public function managementApproval(Request $request, Claim $claim): JsonResponse
    {
        $request->validate([
            'decision' => 'required|in:APPROVE,REQUEST_REVISION,REJECT',
            'notes' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $updated = $this->workflowEngine->transition(
            $claim,
            $request->decision,
            $request->user(),
            $request->only(['notes', 'reason', 'reason_code'])
        );

        return response()->json([
            'success' => true,
            'message' => 'Keputusan Management Approval berhasil diproses.',
            'data' => $updated,
        ]);
    }

    public function requestRevision(Request $request, Claim $claim): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $updated = $this->workflowEngine->transition(
            $claim,
            'REQUEST_REVISION',
            $request->user(),
            $request->only(['reason', 'notes'])
        );

        return response()->json([
            'success' => true,
            'message' => 'Permintaan revisi berhasil dikirim ke pembuat klaim.',
            'data' => $updated,
        ]);
    }

    public function reject(Request $request, Claim $claim): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $updated = $this->workflowEngine->transition(
            $claim,
            'REJECT',
            $request->user(),
            $request->only(['reason', 'notes'])
        );

        return response()->json([
            'success' => true,
            'message' => 'Klaim telah ditolak.',
            'data' => $updated,
        ]);
    }

    public function cancel(Request $request, Claim $claim): JsonResponse
    {
        $updated = $this->workflowEngine->transition(
            $claim,
            'CANCEL',
            $request->user(),
            $request->only(['reason', 'notes'])
        );

        return response()->json([
            'success' => true,
            'message' => 'Klaim telah dibatalkan.',
            'data' => $updated,
        ]);
    }
}
