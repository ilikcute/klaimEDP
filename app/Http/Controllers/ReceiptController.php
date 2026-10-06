<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\ClaimReceipt;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function __construct(protected AuditService $auditService) {}

    public function index(Claim $claim): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $claim->receipts()->with('documents')->get(),
        ]);
    }

    public function store(Request $request, Claim $claim): JsonResponse
    {
        $user = $request->user();

        if (!$claim->current_state->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Nota tidak dapat ditambahkan pada status klaim saat ini.',
            ], 422);
        }

        if ($claim->requester_id !== $user->id && !$user->hasRole(['ADMIN', 'EDP_ADM'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'merchant' => 'required|string|max:200',
            'amount' => 'required|numeric|min:1',
            'receipt_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        $receipt = $claim->receipts()->create($validated);
        $claim->recalculateTotal();

        $this->auditService->log($user, 'RECEIPT_ADDED', ClaimReceipt::class, $receipt->id, null, $receipt->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Nota berhasil ditambahkan.',
            'data' => $receipt,
            'claim_total_amount' => $claim->total_amount,
        ], 201);
    }

    public function update(Request $request, Claim $claim, ClaimReceipt $receipt): JsonResponse
    {
        $user = $request->user();

        if (!$claim->current_state->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Nota tidak dapat diubah pada status klaim saat ini.',
            ], 422);
        }

        if ($claim->requester_id !== $user->id && !$user->hasRole(['ADMIN', 'EDP_ADM'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'transaction_date' => 'sometimes|required|date',
            'merchant' => 'sometimes|required|string|max:200',
            'amount' => 'sometimes|required|numeric|min:1',
            'receipt_number' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        $before = $receipt->toArray();
        $receipt->update($validated);
        $claim->recalculateTotal();

        $this->auditService->log($user, 'RECEIPT_UPDATED', ClaimReceipt::class, $receipt->id, $before, $receipt->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Nota berhasil diperbarui.',
            'data' => $receipt,
            'claim_total_amount' => $claim->total_amount,
        ]);
    }

    public function destroy(Request $request, Claim $claim, ClaimReceipt $receipt): JsonResponse
    {
        $user = $request->user();

        if (!$claim->current_state->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Nota tidak dapat dihapus pada status klaim saat ini.',
            ], 422);
        }

        if ($claim->requester_id !== $user->id && !$user->hasRole(['ADMIN', 'EDP_ADM'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $before = $receipt->toArray();
        $receipt->delete();
        $claim->recalculateTotal();

        $this->auditService->log($user, 'RECEIPT_DELETED', ClaimReceipt::class, $receipt->id, $before, null);

        return response()->json([
            'success' => true,
            'message' => 'Nota berhasil dihapus.',
            'claim_total_amount' => $claim->total_amount,
        ]);
    }
}
