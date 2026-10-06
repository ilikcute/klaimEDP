<?php

namespace App\Services;

use App\Enums\ClaimState;
use App\Models\ApprovalPolicy;
use App\Models\Claim;
use App\Models\ClaimStatusHistory;
use App\Models\User;
use App\Models\WorkflowAction;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowInstance;
use App\Models\WorkflowStage;
use App\Models\WorkflowTransition;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WorkflowEngine
{
    public function __construct(
        protected AuditService $auditService,
        protected SlaService $slaService,
        protected NotificationService $notificationService
    ) {}

    public function getAllowedActions(Claim $claim, User $user): array
    {
        $state = $claim->current_state;
        $actions = [];

        // Admin can perform whatever matches the stage
        $isAdmin = $user->hasRole('ADMIN');
        $isRequester = $claim->requester_id === $user->id;

        switch ($state) {
            case ClaimState::DRAFT:
                if ($isRequester || $isAdmin) {
                    $actions[] = 'SUBMIT';
                    $actions[] = 'CANCEL';
                }
                break;

            case ClaimState::NEED_REVISION:
                if ($isRequester || $isAdmin) {
                    $actions[] = 'RESUBMIT';
                    $actions[] = 'CANCEL';
                }
                break;

            case ClaimState::SUBMITTED:
                if ($user->hasRole(['EDP_ADM', 'ADMIN'])) {
                    $actions[] = 'START_REVIEW';
                    $actions[] = 'FORWARD';
                    $actions[] = 'REQUEST_REVISION';
                    $actions[] = 'REJECT';
                }
                break;

            case ClaimState::EDP_REVIEW:
                if ($user->hasRole(['EDP_ADM', 'ADMIN'])) {
                    $actions[] = 'FORWARD';
                    $actions[] = 'REQUEST_REVISION';
                    $actions[] = 'REJECT';
                }
                break;

            case ClaimState::SUPERVISOR_VERIFICATION:
                if ($user->hasRole(['EDP_SUPERVISOR', 'ADMIN'])) {
                    $actions[] = 'VERIFY';
                    $actions[] = 'REQUEST_REVISION';
                    $actions[] = 'REJECT';
                }
                break;

            case ClaimState::MANAGER_VERIFICATION:
                if ($user->hasRole(['EDP_MANAGER', 'ADMIN'])) {
                    $actions[] = 'VERIFY';
                    $actions[] = 'REQUEST_REVISION';
                    $actions[] = 'REJECT';
                }
                break;

            case ClaimState::FINANCE_VERIFICATION:
                if ($user->hasRole(['FINANCE', 'ADMIN'])) {
                    $actions[] = 'VERIFY';
                    $actions[] = 'REQUEST_REVISION';
                    $actions[] = 'REJECT';
                }
                break;

            case ClaimState::MANAGEMENT_APPROVAL:
                if ($user->hasRole(['DBM', 'BM', 'ADMIN'])) {
                    $actions[] = 'APPROVE';
                    $actions[] = 'REQUEST_REVISION';
                    $actions[] = 'REJECT';
                }
                break;

            case ClaimState::APPROVED:
                if ($user->hasRole(['FINANCE', 'ADMIN'])) {
                    $actions[] = 'PROCESS_PAYMENT';
                }
                break;

            case ClaimState::PAYMENT_PROCESS:
                if ($user->hasRole(['FINANCE', 'ADMIN'])) {
                    $actions[] = 'COMPLETE_PAYMENT';
                }
                break;

            default:
                break;
        }

        return $actions;
    }

    public function transition(
        Claim $claim,
        string $action,
        User $actor,
        array $params = []
    ): Claim {
        return DB::transaction(function () use ($claim, $action, $actor, $params) {
            // Concurrency protection: lock the claim row
            /** @var Claim $lockedClaim */
            $lockedClaim = Claim::where('id', $claim->id)->lockForUpdate()->firstOrFail();

            // Validate actor capability
            $allowed = $this->getAllowedActions($lockedClaim, $actor);
            if (!in_array($action, $allowed)) {
                throw new ConflictHttpException("Tindakan '{$action}' tidak diperbolehkan pada status klaim saat ini ({$lockedClaim->current_state->value}) atau Anda tidak memiliki hak akses.");
            }

            // Ensure receipts exist if submitting/resubmitting
            if (in_array($action, ['SUBMIT', 'RESUBMIT'])) {
                if ($lockedClaim->receipts()->count() === 0) {
                    throw new HttpException(422, 'Klaim harus memiliki minimal satu nota sebelum diajukan.');
                }
                $lockedClaim->recalculateTotal();
            }

            $fromState = $lockedClaim->current_state;
            $toState = $this->resolveNextState($fromState, $action);

            $reason = $params['reason'] ?? $params['reason_code'] ?? null;
            $notes = $params['notes'] ?? null;

            // Resolve approval policy if management approval stage
            $approvalPolicy = null;
            if ($fromState === ClaimState::MANAGEMENT_APPROVAL && $action === 'APPROVE') {
                $approvalPolicy = $this->resolveManagementApprovalPolicy($lockedClaim, $actor);
            }

            // Get or create WorkflowInstance
            $workflowInstance = $lockedClaim->workflowInstance;
            if (!$workflowInstance) {
                $wfDef = WorkflowDefinition::where('is_active', true)->first();
                $workflowInstance = WorkflowInstance::create([
                    'claim_id' => $lockedClaim->id,
                    'workflow_definition_id' => $wfDef ? $wfDef->id : 1,
                    'workflow_version' => $wfDef ? $wfDef->version : 1,
                    'started_at' => now(),
                ]);
            }

            // Find transition if registered
            $transition = WorkflowTransition::where('from_state', $fromState->value)
                ->where('action', $action)
                ->first();

            // Record WorkflowAction
            $primaryRole = $actor->roles()->first()?->name ?? 'USER';
            WorkflowAction::create([
                'claim_id' => $lockedClaim->id,
                'workflow_instance_id' => $workflowInstance->id,
                'transition_id' => $transition?->id,
                'from_state' => $fromState->value,
                'action' => $action,
                'to_state' => $toState->value,
                'actor_id' => $actor->id,
                'actor_role' => $primaryRole,
                'reason' => $reason,
                'notes' => $notes,
                'approval_policy_id' => $approvalPolicy?->id,
                'approval_policy_version' => $approvalPolicy?->version,
                'created_at' => now(),
            ]);

            // Record ClaimStatusHistory (append-only)
            ClaimStatusHistory::create([
                'claim_id' => $lockedClaim->id,
                'from_state' => $fromState->value,
                'to_state' => $toState->value,
                'action' => $action,
                'actor_id' => $actor->id,
                'reason' => $reason,
                'notes' => $notes,
                'created_at' => now(),
            ]);

            // Update Claim
            $beforeData = $lockedClaim->toArray();
            $lockedClaim->current_state = $toState;

            if ($action === 'SUBMIT' || $action === 'RESUBMIT') {
                $lockedClaim->submitted_at = now();
            }
            if ($toState === ClaimState::APPROVED) {
                $lockedClaim->approved_at = now();
            }
            if ($toState === ClaimState::PAID) {
                $lockedClaim->paid_at = now();
                $workflowInstance->completed_at = now();
                $workflowInstance->save();
            }
            $lockedClaim->save();

            // SLA tracking
            $nextStage = WorkflowStage::where('code', $toState->value)->first();
            if ($nextStage && !$toState->isTerminal()) {
                $this->slaService->startStageSla($lockedClaim, $nextStage);
            } else {
                $this->slaService->completeOpenSlas($lockedClaim);
            }

            // Audit Event
            $this->auditService->log(
                $actor,
                'CLAIM_TRANSITION',
                Claim::class,
                $lockedClaim->id,
                ['state' => $fromState->value],
                ['state' => $toState->value, 'action' => $action, 'notes' => $notes]
            );

            // Notify Requester
            if ($lockedClaim->requester) {
                $this->notificationService->notify(
                    $lockedClaim->requester,
                    'CLAIM_STATUS_UPDATED',
                    "Status Klaim {$lockedClaim->claim_number}",
                    "Klaim Anda telah diperbarui ke status: {$toState->label()}" . ($notes ? " ({$notes})" : ""),
                    Claim::class,
                    $lockedClaim->id
                );
            }

            // Notify Next Approver/Role
            $this->notifyNextRole($toState, $lockedClaim);

            return $lockedClaim;
        });
    }

    protected function resolveNextState(ClaimState $current, string $action): ClaimState
    {
        return match ($action) {
            'SUBMIT', 'RESUBMIT' => ClaimState::SUBMITTED,
            'START_REVIEW' => ClaimState::EDP_REVIEW,
            'FORWARD' => ClaimState::SUPERVISOR_VERIFICATION,
            'VERIFY' => match ($current) {
                ClaimState::SUPERVISOR_VERIFICATION => ClaimState::MANAGER_VERIFICATION,
                ClaimState::MANAGER_VERIFICATION => ClaimState::FINANCE_VERIFICATION,
                ClaimState::FINANCE_VERIFICATION => ClaimState::MANAGEMENT_APPROVAL,
                default => throw new HttpException(422, "Invalid verify from state: {$current->value}"),
            },
            'APPROVE' => ClaimState::APPROVED,
            'PROCESS_PAYMENT' => ClaimState::PAYMENT_PROCESS,
            'COMPLETE_PAYMENT' => ClaimState::PAID,
            'REQUEST_REVISION' => ClaimState::NEED_REVISION,
            'REJECT' => ClaimState::REJECTED,
            'CANCEL' => ClaimState::CANCELLED,
            default => throw new HttpException(422, "Tindakan tidak dikenal: {$action}"),
        };
    }

    protected function resolveManagementApprovalPolicy(Claim $claim, User $actor): ?ApprovalPolicy
    {
        // Must match stage MANAGEMENT_APPROVAL and active policy
        $stage = WorkflowStage::where('code', 'MANAGEMENT_APPROVAL')->first();
        if (!$stage) return null;

        $policy = ApprovalPolicy::where('workflow_stage_id', $stage->id)
            ->where('is_active', true)
            ->where('effective_from', '<=', now()->toDateString())
            ->where(function ($q) {
                $q->whereNull('effective_until')->orWhere('effective_until', '>=', now()->toDateString());
            })
            ->with('roles')
            ->orderByDesc('priority')
            ->first();

        return $policy;
    }

    protected function notifyNextRole(ClaimState $state, Claim $claim): void
    {
        $roleMap = [
            ClaimState::SUBMITTED->value => 'EDP_ADM',
            ClaimState::EDP_REVIEW->value => 'EDP_ADM',
            ClaimState::SUPERVISOR_VERIFICATION->value => 'EDP_SUPERVISOR',
            ClaimState::MANAGER_VERIFICATION->value => 'EDP_MANAGER',
            ClaimState::FINANCE_VERIFICATION->value => 'FINANCE',
            ClaimState::MANAGEMENT_APPROVAL->value => 'BM',
            ClaimState::APPROVED->value => 'FINANCE',
            ClaimState::PAYMENT_PROCESS->value => 'FINANCE',
        ];

        if (isset($roleMap[$state->value])) {
            $role = $roleMap[$state->value];
            $this->notificationService->notifyRole(
                $role,
                'CLAIM_NEEDS_ACTION',
                "Tugas Baru: Klaim {$claim->claim_number}",
                "Klaim {$claim->claim_number} memerlukan tindakan Anda pada tahap {$state->label()}.",
                Claim::class,
                $claim->id
            );
        }
    }
}
