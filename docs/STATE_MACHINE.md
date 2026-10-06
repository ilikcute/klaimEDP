# STATE MACHINE

## Sistem Rekap, Tracking & Analisa Klaim / Reimbursement EDP

**Version:** 1.0
**Status:** Architecture Baseline
**Backend:** Laravel
**Database:** MySQL 8+
**Frontend:** Vue.js

---

# 1. Tujuan

Dokumen ini mendefinisikan state machine formal untuk lifecycle Claim/Reimbursement.

State machine menjadi single source of truth untuk:

* status claim;
* valid transition;
* actor yang berhak melakukan transition;
* guard;
* action;
* revision routing;
* rejection;
* approval;
* payment;
* concurrency protection;
* audit trail.

Tidak boleh ada controller/frontend yang mengimplementasikan workflow sendiri-sendiri.

---

# 2. Core Principle

Claim tidak boleh berpindah state hanya karena frontend mengirim:

```text
current_state = APPROVED
```

State hanya dapat berubah melalui:

```text
Authorized Action
        ↓
Guard Validation
        ↓
Transactional Transition
        ↓
State Change
        ↓
History
        ↓
Audit
        ↓
Domain Event
```

---

# 3. State Enumeration

State utama:

```text
DRAFT
SUBMITTED
EDP_REVIEW
SUPERVISOR_VERIFICATION
MANAGER_VERIFICATION
FINANCE_VERIFICATION
MANAGEMENT_APPROVAL
APPROVED
PAYMENT_PROCESS
PAID
NEED_REVISION
REJECTED
CANCELLED
```

---

# 4. State Categories

## Editable

```text
DRAFT
NEED_REVISION
```

Requester dapat memperbaiki claim pada state ini sesuai permission.

---

## Processing

```text
SUBMITTED
EDP_REVIEW
SUPERVISOR_VERIFICATION
MANAGER_VERIFICATION
FINANCE_VERIFICATION
MANAGEMENT_APPROVAL
PAYMENT_PROCESS
```

---

## Terminal

```text
PAID
REJECTED
CANCELLED
```

Terminal state tidak dapat diubah melalui normal workflow.

---

# 5. High-Level State Graph

```text
                           ┌───────────────┐
                           │     DRAFT     │
                           └───────┬───────┘
                                   │ SUBMIT
                                   ▼
                           ┌───────────────┐
                           │   SUBMITTED   │
                           └───────┬───────┘
                                   │
                                   ▼
                           ┌───────────────┐
                           │   EDP REVIEW  │
                           └───────┬───────┘
                                   │ FORWARD
                                   ▼
                     ┌─────────────────────────┐
                     │ SUPERVISOR VERIFICATION │
                     └────────────┬────────────┘
                                  │ VERIFY
                                  ▼
                     ┌─────────────────────────┐
                     │  MANAGER VERIFICATION   │
                     └────────────┬────────────┘
                                  │ VERIFY
                                  ▼
                     ┌─────────────────────────┐
                     │  FINANCE VERIFICATION   │
                     └────────────┬────────────┘
                                  │ VERIFY
                                  ▼
                     ┌─────────────────────────┐
                     │  MANAGEMENT APPROVAL    │
                     │                         │
                     │       DBM OR BM         │
                     └────────────┬────────────┘
                                  │ APPROVE
                                  ▼
                           ┌───────────────┐
                           │   APPROVED    │
                           └───────┬───────┘
                                   │ PROCESS PAYMENT
                                   ▼
                           ┌───────────────┐
                           │ PAYMENT_PROCESS│
                           └───────┬───────┘
                                   │ PAID
                                   ▼
                           ┌───────────────┐
                           │      PAID     │
                           └───────────────┘
```

---

# 6. Revision Routing

Revision merupakan bagian penting dari workflow.

```text
EDP_REVIEW
      │
      └── REQUEST_REVISION
              ↓
        NEED_REVISION

SUPERVISOR_VERIFICATION
      │
      └── REQUEST_REVISION
              ↓
        NEED_REVISION

MANAGER_VERIFICATION
      │
      └── REQUEST_REVISION
              ↓
        NEED_REVISION

FINANCE_VERIFICATION
      │
      └── REQUEST_REVISION
              ↓
        NEED_REVISION

MANAGEMENT_APPROVAL
      │
      └── REQUEST_REVISION
              ↓
        NEED_REVISION
```

Setelah requester memperbaiki:

```text
NEED_REVISION
      │
      │ RESUBMIT
      ▼
SUBMITTED
```

Kemudian claim kembali melalui workflow.

---

# 7. Revision Principle

Revision tidak boleh langsung mengembalikan claim ke stage tempat revision dilakukan.

Contoh:

```text
MANAGER_VERIFICATION
        ↓
REQUEST_REVISION
        ↓
NEED_REVISION
        ↓
RESUBMIT
        ↓
SUBMITTED
        ↓
EDP_REVIEW
        ↓
SUPERVISOR
        ↓
MANAGER
```

Alasannya:

* perubahan data dapat memengaruhi validasi sebelumnya;
* dokumen mungkin berubah;
* total dapat berubah;
* policy check harus diulang;
* audit trail tetap lengkap.

---

# 8. Transition Table

| Current State           | Action           | Next State              | Actor      |
| ----------------------- | ---------------- | ----------------------- | ---------- |
| DRAFT                   | SUBMIT           | SUBMITTED               | Requester  |
| NEED_REVISION           | RESUBMIT         | SUBMITTED               | Requester  |
| SUBMITTED               | START_REVIEW     | EDP_REVIEW              | EDP Adm    |
| EDP_REVIEW              | FORWARD          | SUPERVISOR_VERIFICATION | EDP Adm    |
| EDP_REVIEW              | REQUEST_REVISION | NEED_REVISION           | EDP Adm    |
| EDP_REVIEW              | REJECT           | REJECTED                | EDP Adm    |
| SUPERVISOR_VERIFICATION | VERIFY           | MANAGER_VERIFICATION    | Supervisor |
| SUPERVISOR_VERIFICATION | REQUEST_REVISION | NEED_REVISION           | Supervisor |
| SUPERVISOR_VERIFICATION | REJECT           | REJECTED                | Supervisor |
| MANAGER_VERIFICATION    | VERIFY           | FINANCE_VERIFICATION    | Manager    |
| MANAGER_VERIFICATION    | REQUEST_REVISION | NEED_REVISION           | Manager    |
| MANAGER_VERIFICATION    | REJECT           | REJECTED                | Manager    |
| FINANCE_VERIFICATION    | VERIFY           | MANAGEMENT_APPROVAL     | Finance    |
| FINANCE_VERIFICATION    | REQUEST_REVISION | NEED_REVISION           | Finance    |
| FINANCE_VERIFICATION    | REJECT           | REJECTED                | Finance    |
| MANAGEMENT_APPROVAL     | APPROVE          | APPROVED                | DBM OR BM  |
| MANAGEMENT_APPROVAL     | REQUEST_REVISION | NEED_REVISION           | DBM OR BM  |
| MANAGEMENT_APPROVAL     | REJECT           | REJECTED                | DBM OR BM  |
| APPROVED                | PROCESS_PAYMENT  | PAYMENT_PROCESS         | Finance    |
| PAYMENT_PROCESS         | COMPLETE_PAYMENT | PAID                    | Finance    |

---

# 9. EDP Review

EDP Adm melakukan initial review.

Guard:

```text
actor.hasPermission('claim.review')
AND
claim.state == EDP_REVIEW
```

Checks:

* requester valid;
* claim type valid;
* receipts lengkap;
* documents tersedia sesuai requirement;
* total valid;
* data tidak corrupt.

---

# 10. Supervisor Verification

Guard:

```text
actor.role == EDP_SUPERVISOR
AND
claim.state == SUPERVISOR_VERIFICATION
```

Checks:

* requester;
* receipt;
* amount;
* document;
* business rule;
* BBM;
* Health;
* potential duplicate.

Potential duplicate adalah warning, bukan automatic rejection.

---

# 11. Manager Verification

Guard:

```text
actor.role == EDP_MANAGER
AND
claim.state == MANAGER_VERIFICATION
```

Manager tidak dapat melewati Supervisor.

Tidak valid:

```text
EDP_REVIEW → MANAGER_VERIFICATION
```

---

# 12. Finance Verification

Guard:

```text
actor.hasPermission('claim.finance_verify')
AND
claim.state == FINANCE_VERIFICATION
```

Checks:

* calculation;
* supporting document;
* policy;
* final amount;
* payment readiness;
* duplicate warning;
* financial consistency.

---

# 13. Management Approval Gate

State:

```text
MANAGEMENT_APPROVAL
```

Eligible actor:

```text
DBM
OR
BM
```

Rule:

```text
DBM approval = gate satisfied

OR

BM approval = gate satisfied
```

Tidak membutuhkan:

```text
DBM approval AND BM approval
```

---

# 14. Management Approval Guard

Approval diperbolehkan jika:

```text
claim.state == MANAGEMENT_APPROVAL
AND actor.isAuthorizedManagementApprover
AND claim has passed Finance Verification
AND claim is not rejected
```

Jika DBM approve:

```text
MANAGEMENT_APPROVAL
        ↓
APPROVED
```

Jika BM approve:

```text
MANAGEMENT_APPROVAL
        ↓
APPROVED
```

---

# 15. Management Approval Audit

System wajib menyimpan:

```text
actor_id
actor_role
decision
timestamp
notes
```

Contoh:

```text
actor_role = DBM
decision = APPROVE
```

atau:

```text
actor_role = BM
decision = APPROVE
```

---

# 16. Payment Transition

Guard:

```text
claim.state == APPROVED
AND
actor.hasPermission('claim.pay')
AND
payment does not already exist as PAID
```

Transaction:

```text
BEGIN

lock claim

validate state
validate amount
validate authorization
create/update payment
change state

COMMIT
```

---

# 17. Terminal State

`PAID` tidak memiliki transition normal.

```text
PAID → X
```

invalid.

Koreksi setelah payment harus menggunakan controlled adjustment/reversal process pada future phase.

---

# 18. Rejection

Reject adalah terminal.

```text
REJECTED
```

Tidak dapat langsung:

```text
REJECTED → APPROVED
```

Jika organisasi membutuhkan pengajuan ulang, harus dibuat claim baru atau controlled reopen process.

---

# 19. Cancellation

Cancellation diperbolehkan hanya pada state tertentu.

Default:

```text
DRAFT → CANCELLED
NEED_REVISION → CANCELLED
```

Requester dapat cancel claim miliknya sendiri jika policy mengizinkan.

Processing claim tidak boleh dibatalkan tanpa authorized administrative process.

---

# 20. Guard

Guard merupakan kondisi yang harus benar sebelum transition.

Contoh:

```text
canSubmit()
canReview()
canVerify()
canApprove()
canPay()
```

Guard harus berada di backend/domain layer.

Frontend hanya menampilkan action berdasarkan informasi guard, tetapi tidak menjadi enforcement.

---

# 21. Transition Transaction

Setiap transition penting:

```text
BEGIN
  lock claim
  reload current state

  evaluate guard

  execute action

  update claim state
  insert workflow action
  insert status history
  insert audit event
  create outbox event

COMMIT
```

---

# 22. Concurrency

Jika dua user melakukan action pada claim yang sama:

```text
User A ──┐
         ├── SELECT FOR UPDATE
User B ──┘
```

Hanya transaction pertama yang memperoleh valid state transition.

Transaction kedua harus mengevaluasi ulang:

```text
current_state
```

dan mendapatkan:

```text
409 Conflict
```

jika transition sudah tidak valid.

---

# 23. Idempotency

Untuk action critical:

```text
SUBMIT
VERIFY
APPROVE
PROCESS_PAYMENT
COMPLETE_PAYMENT
```

gunakan idempotency key.

Rule:

```text
same key + same payload
→ replay

same key + different payload
→ 409
```

---

# 24. Action vs State

Frontend tidak boleh mengubah state.

Frontend mengirim:

```text
action = VERIFY
```

Backend menentukan:

```text
current_state
+
actor
+
guard
=
next_state
```

---

# 25. Workflow Versioning

Workflow instance menyimpan:

```text
workflow_version
```

Contoh:

```text
REIMBURSEMENT_V1
```

Jika workflow berubah menjadi:

```text
REIMBURSEMENT_V2
```

claim lama tetap menggunakan version sebelumnya.

---

# 26. State Machine Service

Recommended Laravel abstraction:

```text
ClaimWorkflowService
```

Responsibilities:

* resolve current state;
* validate transition;
* authorize action;
* execute guard;
* persist transition;
* emit domain event.

Controller tidak boleh memiliki business transition logic yang panjang.

---

# 27. Transition Result

Service mengembalikan result:

```json
{
  "claim_id": 123,
  "previous_state": "MANAGEMENT_APPROVAL",
  "action": "APPROVE",
  "new_state": "APPROVED"
}
```

---

# 28. Invariants

Setiap transition wajib menjaga:

```text
INV-001
State selalu valid.

INV-002
Action hanya dilakukan actor authorized.

INV-003
Current state sesuai database.

INV-004
History selalu tercatat.

INV-005
Audit selalu tercatat.

INV-006
Management approval = DBM OR BM.

INV-007
Payment hanya setelah APPROVED.

INV-008
PAID tidak dapat dibayar ulang.

INV-009
Revision menghasilkan NEED_REVISION.

INV-010
Revision RESUBMIT kembali ke SUBMITTED.

INV-011
Terminal state tidak memiliki normal transition.
```

---

# 29. Revision Routing Policy

Default:

```text
Any Review/Verification/Approval
             ↓
       NEED_REVISION
             ↓
        RESUBMIT
             ↓
         SUBMITTED
```

Namun system menyimpan:

```text
revision_requested_by
revision_reason
revision_notes
revision_count
revision_requested_at
```

untuk analytics.

---

# 30. SLA Interaction

SLA stage dimulai ketika claim memasuki stage.

Contoh:

```text
SUPERVISOR_VERIFICATION
        ↓
SLA START
```

Ketika:

```text
VERIFY
```

maka:

```text
SLA COMPLETE
```

Kemudian stage berikutnya dimulai.

Untuk revision:

```text
NEED_REVISION
```

memiliki SLA requester jika organisasi menginginkan tracking response time.

---

# 31. Notification Interaction

Transition menghasilkan domain event:

```text
ClaimSubmitted
ClaimNeedsRevision
ClaimVerified
ClaimApproved
ClaimRejected
PaymentCompleted
```

Event dikonsumsi notification handler.

Telegram failure tidak rollback claim transaction.

---

# 32. State Machine Testing

Minimal test matrix:

```text
DRAFT → SUBMIT
DRAFT → APPROVE          INVALID
DRAFT → PAY              INVALID

EDP_REVIEW → FORWARD
EDP_REVIEW → VERIFY      INVALID

SUPERVISOR → MANAGER
MANAGER → FINANCE
FINANCE → MANAGEMENT

MANAGEMENT → APPROVE by DBM
MANAGEMENT → APPROVE by BM

MANAGEMENT → APPROVE by EDP_USER   INVALID

APPROVED → PAYMENT_PROCESS
PAYMENT_PROCESS → PAID

PAID → APPROVED           INVALID
REJECTED → PAID           INVALID
```

---

# 33. Definition of Done

* [ ] Semua state didefinisikan.
* [ ] Semua transition didefinisikan.
* [ ] Actor authorization didefinisikan.
* [ ] Guard didefinisikan.
* [ ] Revision routing didefinisikan.
* [ ] DBM OR BM gate didefinisikan.
* [ ] Payment transition didefinisikan.
* [ ] Terminal state didefinisikan.
* [ ] Concurrency handling didefinisikan.
* [ ] Idempotency didefinisikan.
* [ ] Audit didefinisikan.
* [ ] SLA interaction didefinisikan.
* [ ] Notification interaction didefinisikan.
* [ ] Test matrix tersedia.

---

**End of STATE_MACHINE.md**
