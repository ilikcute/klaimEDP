<?php

namespace App\Http\Controllers;

use App\Enums\ClaimState;
use App\Enums\PaymentStatus;
use App\Models\Claim;
use App\Models\Payment;
use App\Services\AuditService;
use App\Services\WorkflowEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct(
        protected WorkflowEngine $workflowEngine,
        protected AuditService $auditService
    ) {}

    public function show(Claim $claim): JsonResponse
    {
        $payment = $claim->payment()->with(['processor', 'proofDocument'])->first();

        return response()->json([
            'success' => true,
            'data' => $payment,
        ]);
    }

    public function process(Request $request, Claim $claim): JsonResponse
    {
        $user = $request->user();

        if (!$user->hasRole(['FINANCE', 'ADMIN'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Hanya Finance atau Admin yang dapat memproses pembayaran.'], 403);
        }

        if (!in_array($claim->current_state, [ClaimState::APPROVED, ClaimState::PAYMENT_PROCESS])) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran hanya dapat dilakukan untuk klaim berstatus APPROVED atau PAYMENT_PROCESS.',
            ], 422);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'bank_name' => 'required|string|max:150',
            'account_number' => 'required|string|max:100',
            'account_name' => 'required|string|max:150',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:150',
            'notes' => 'nullable|string',
            'is_final' => 'boolean', // If true, transition to PAID, else PAYMENT_PROCESS
        ]);

        if ($validated['amount'] > $claim->total_amount) {
            return response()->json([
                'success' => false,
                'message' => "Nominal pembayaran (Rp {$validated['amount']}) melebihi total klaim yang disetujui (Rp {$claim->total_amount}).",
            ], 422);
        }

        $idempotencyKey = $request->header('Idempotency-Key') ?? $request->idempotency_key;

        $payment = DB::transaction(function () use ($claim, $validated, $user, $idempotencyKey) {
            if ($idempotencyKey) {
                $existing = Payment::where('idempotency_key', $idempotencyKey)->first();
                if ($existing) {
                    if ((float)$existing->amount === (float)$validated['amount'] && $existing->bank_name === $validated['bank_name']) {
                        return $existing;
                    }
                    abort(409, 'Idempotency key telah digunakan dengan data transaksi yang berbeda.');
                }
            }

            $isFinal = !empty($validated['is_final']);
            $status = $isFinal ? PaymentStatus::PAID : PaymentStatus::PROCESSING;

            $payment = Payment::updateOrCreate(
                ['claim_id' => $claim->id],
                [
                    'processed_by' => $user->id,
                    'amount' => $validated['amount'],
                    'payment_method' => 'BANK_TRANSFER',
                    'bank_name' => $validated['bank_name'],
                    'bank_account_number' => $validated['account_number'],
                    'bank_account_name' => $validated['account_name'],
                    'payment_date' => $validated['payment_date'],
                    'transfer_reference' => $validated['reference_number'] ?? null,
                    'status' => $status,
                    'idempotency_key' => $idempotencyKey,
                    'notes' => $validated['notes'] ?? null,
                ]
            );

            // Transition claim state
            if ($isFinal) {
                $this->workflowEngine->transition(
                    $claim,
                    $claim->current_state === ClaimState::APPROVED ? 'PROCESS_PAYMENT' : 'COMPLETE_PAYMENT',
                    $user,
                    ['notes' => 'Pembayaran lunas: ' . ($validated['reference_number'] ?? '')]
                );

                if ($claim->fresh()->current_state === ClaimState::PAYMENT_PROCESS) {
                    $this->workflowEngine->transition(
                        $claim->fresh(),
                        'COMPLETE_PAYMENT',
                        $user,
                        ['notes' => 'Pembayaran selesai.']
                    );
                }
            } else {
                if ($claim->current_state === ClaimState::APPROVED) {
                    $this->workflowEngine->transition(
                        $claim,
                        'PROCESS_PAYMENT',
                        $user,
                        ['notes' => 'Pembayaran sedang diproses.']
                    );
                }
            }

            $this->auditService->log($user, 'PAYMENT_PROCESSED', Payment::class, $payment->id, null, $payment->toArray());

            return $payment;
        });

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil diproses.',
            'data' => $payment->load(['processor', 'claim']),
        ]);
    }
}
