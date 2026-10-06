<?php

namespace App\Http\Controllers;

use App\Enums\ClaimState;
use App\Enums\ClaimType;
use App\Models\Claim;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Services\AuditService;
use App\Services\BbmService;
use App\Services\HealthService;
use App\Services\WorkflowEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClaimController extends Controller
{
    public function __construct(
        protected WorkflowEngine $workflowEngine,
        protected AuditService $auditService,
        protected BbmService $bbmService,
        protected HealthService $healthService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Claim::with(['requester', 'bbmDetail.vehicle', 'healthDetail', 'payment'])
            ->forUser($user);

        $sortBy = $request->get('sort_by', 'claim_number');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (in_array($sortBy, ['claim_number', 'claim_date', 'total_amount', 'created_at', 'id'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('claim_number', 'asc');
        }

        if ($request->filled('status')) {
            $query->where('current_state', $request->status);
        }

        if ($request->filled('claim_type')) {
            $query->where('claim_type', $request->claim_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('claim_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('requester', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('claim_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('claim_date', '<=', $request->date_to);
        }

        $perPage = min((int)($request->per_page ?? 20), 100);
        $claims = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $claims->items(),
            'pagination' => [
                'current_page' => $claims->currentPage(),
                'last_page' => $claims->lastPage(),
                'per_page' => $claims->perPage(),
                'total' => $claims->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'claim_type' => 'required|string|in:BBM,TOL,HEALTH',
            'claim_date' => 'required|date',
            'description' => 'nullable|string',
            // Optional BBM specific fields if provided on create
            'vehicle_id' => 'nullable|required_if:claim_type,BBM|exists:vehicles,id',
            'distance_km' => 'nullable|required_if:claim_type,BBM|numeric|min:0.1',
            'fuel_liter' => 'nullable|required_if:claim_type,BBM|numeric|min:0.01',
            'last_odometer_km' => 'nullable|numeric|min:0',
            'current_odometer_km' => 'nullable|numeric|min:0',
        ]);

        $user = $request->user();

        $claim = DB::transaction(function () use ($validated, $user) {
            $claim = Claim::create([
                'claim_number' => Claim::generateClaimNumber(),
                'requester_id' => $user->id,
                'claim_type' => ClaimType::from($validated['claim_type']),
                'claim_date' => $validated['claim_date'],
                'description' => $validated['description'] ?? null,
                'total_amount' => 0.00,
                'current_state' => ClaimState::DRAFT,
            ]);

            $wfDef = WorkflowDefinition::where('is_active', true)->first();
            WorkflowInstance::create([
                'claim_id' => $claim->id,
                'workflow_definition_id' => $wfDef ? $wfDef->id : 1,
                'workflow_version' => $wfDef ? $wfDef->version : 1,
                'started_at' => now(),
            ]);

            if ($claim->claim_type === ClaimType::BBM && !empty($validated['vehicle_id'])) {
                $this->bbmService->calculateAndAttach(
                    $claim,
                    (int)$validated['vehicle_id'],
                    (float)$validated['distance_km'],
                    (float)$validated['fuel_liter'],
                    isset($validated['last_odometer_km']) ? (float)$validated['last_odometer_km'] : null,
                    isset($validated['current_odometer_km']) ? (float)$validated['current_odometer_km'] : null
                );
            } elseif ($claim->claim_type === ClaimType::HEALTH) {
                $this->healthService->attachHealthDetail($claim);
            }

            $this->auditService->log($user, 'CLAIM_CREATED', Claim::class, $claim->id, null, $claim->toArray());

            return $claim;
        });

        return response()->json([
            'success' => true,
            'message' => 'Klaim berhasil dibuat sebagai DRAFT.',
            'data' => $claim->load(['bbmDetail.vehicle', 'healthDetail.policy']),
        ], 201);
    }

    public function show(Request $request, Claim $claim): JsonResponse
    {
        $user = $request->user();

        // Check view authorization
        if (!$user->hasRole('ADMIN') && $user->hasRole('EDP_OPERASIONAL') && $claim->requester_id !== $user->id) {
            if (!$user->hasRole(['EDP_ADM', 'EDP_SUPERVISOR', 'EDP_MANAGER', 'FINANCE', 'DBM', 'BM'])) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }
        }

        $claim->load([
            'requester.roles',
            'receipts.documents',
            'documents.uploader',
            'bbmDetail.vehicle',
            'healthDetail.policy',
            'workflowActions.actor',
            'statusHistories.actor',
            'slaInstances.stage',
            'payment.processor',
            'payment.proofDocument',
        ]);

        $allowedActions = $this->workflowEngine->getAllowedActions($claim, $user);

        return response()->json([
            'success' => true,
            'data' => array_merge($claim->toArray(), [
                'allowed_actions' => $allowedActions,
            ]),
        ]);
    }

    public function update(Request $request, Claim $claim): JsonResponse
    {
        $user = $request->user();

        if (!$claim->current_state->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Klaim tidak dapat diedit pada status saat ini.',
            ], 422);
        }

        if ($claim->requester_id !== $user->id && !$user->hasRole('ADMIN')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'claim_date' => 'sometimes|required|date',
            'description' => 'nullable|string',
        ]);

        $before = $claim->toArray();
        $claim->update($validated);
        $this->auditService->log($user, 'CLAIM_UPDATED', Claim::class, $claim->id, $before, $claim->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Data klaim berhasil diperbarui.',
            'data' => $claim,
        ]);
    }

    public function destroy(Request $request, Claim $claim): JsonResponse
    {
        $user = $request->user();

        if ($claim->current_state !== ClaimState::DRAFT) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya klaim berstatus DRAFT yang dapat dihapus.',
            ], 422);
        }

        if ($claim->requester_id !== $user->id && !$user->hasRole('ADMIN')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $claim->delete();
        $this->auditService->log($user, 'CLAIM_DELETED', Claim::class, $claim->id, $claim->toArray(), null);

        return response()->json([
            'success' => true,
            'message' => 'Klaim draft berhasil dihapus.',
        ]);
    }
}
