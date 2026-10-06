# WORKFLOW

## Sistem Rekap, Tracking & Analisa Klaim / Reimbursement EDP

**Version:** 1.0
**Status:** Draft for Architecture & Development

---

# 1. Tujuan

Dokumen ini mendefinisikan state machine untuk lifecycle klaim.

Workflow harus:

* deterministic;
* auditable;
* configurable;
* concurrency-safe;
* authorization-aware;
* tidak bergantung pada frontend;
* dapat diperluas tanpa redesign fundamental.

---

# 2. Core Principle

Jangan memodelkan workflow dengan banyak kolom seperti:

```text
approved_by_dbm
approved_by_bm
verified_by_supervisor
verified_by_manager
```

sebagai sumber kebenaran workflow.

Gunakan:

```text
Claim
   │
   ├── Current State
   │
   ├── Workflow Instance
   │
   ├── Workflow Actions
   │
   ├── Status History
   │
   └── Audit Trail
```

Dengan pendekatan ini workflow dapat berkembang tanpa perubahan fundamental pada model claim.

---

# 3. State Machine

Core lifecycle:

```text
DRAFT
  ↓
SUBMITTED
  ↓
EDP_REVIEW
  ↓
SUPERVISOR_VERIFICATION
  ↓
MANAGER_VERIFICATION
  ↓
FINANCE_VERIFICATION
  ↓
DBM_APPROVAL
  ↓
BM_APPROVAL
  ↓
APPROVED
  ↓
PAYMENT_PROCESS
  ↓
PAID
```

Terminal states:

```text
PAID
REJECTED
CANCELLED
```

Revision dapat mengembalikan claim ke state sebelumnya sesuai policy.

---

# 4. State Definitions

## 4.1 DRAFT

Claim sedang dibuat.

Allowed:

```text
EDIT
ADD_RECEIPT
REMOVE_RECEIPT
UPLOAD_DOCUMENT
DELETE_DOCUMENT
SUBMIT
CANCEL
```

---

## 4.2 SUBMITTED

Claim sudah dikirim dan menunggu review.

Requester tidak boleh mengubah data substantif kecuali melalui revision workflow.

---

## 4.3 EDP_REVIEW

EDP Adm melakukan administrative review.

Actions:

```text
FORWARD
REQUEST_REVISION
REJECT
```

---

## 4.4 SUPERVISOR_VERIFICATION

Supervisor melakukan verification.

Actions:

```text
VERIFY
REQUEST_REVISION
REJECT
```

---

## 4.5 MANAGER_VERIFICATION

Manager melakukan verification.

Actions:

```text
VERIFY
REQUEST_REVISION
REJECT
```

---

## 4.6 FINANCE_VERIFICATION

GA/Finance melakukan final verification.

Actions:

```text
VERIFY
REQUEST_REVISION
REJECT
```

---

## 4.7 DBM_APPROVAL

DBM melakukan management approval.

Actions:

```text
APPROVE
REQUEST_REVISION
REJECT
```

---

## 4.8 BM_APPROVAL

BM melakukan final management approval.

Actions:

```text
APPROVE
REQUEST_REVISION
REJECT
```

---

## 4.9 APPROVED

Claim sudah mendapatkan approval akhir.

Tidak boleh dilakukan perubahan substantif terhadap receipt/amount tanpa controlled correction workflow.

---

## 4.10 PAYMENT_PROCESS

Finance sedang memproses pembayaran.

Actions:

```text
PROCESS_PAYMENT
```

Payment harus idempotent.

---

## 4.11 PAID

Pembayaran berhasil.

Ini merupakan terminal state normal.

---

## 4.12 REJECTED

Claim ditolak.

Alasan wajib.

Claim tidak boleh kembali ke workflow biasa tanpa explicit re-open policy.

---

## 4.13 CANCELLED

Claim dibatalkan.

Alasan wajib.

---

# 5. State Transition Matrix

| Current State           | Action           | Next State              | Actor            |
| ----------------------- | ---------------- | ----------------------- | ---------------- |
| DRAFT                   | SUBMIT           | SUBMITTED               | Owner/Adm        |
| SUBMITTED               | START_REVIEW     | EDP_REVIEW              | EDP Adm          |
| EDP_REVIEW              | FORWARD          | SUPERVISOR_VERIFICATION | EDP Adm          |
| EDP_REVIEW              | REQUEST_REVISION | DRAFT/REVISION          | EDP Adm          |
| EDP_REVIEW              | REJECT           | REJECTED                | EDP Adm          |
| SUPERVISOR_VERIFICATION | VERIFY           | MANAGER_VERIFICATION    | Supervisor       |
| SUPERVISOR_VERIFICATION | REQUEST_REVISION | REVISION                | Supervisor       |
| SUPERVISOR_VERIFICATION | REJECT           | REJECTED                | Supervisor       |
| MANAGER_VERIFICATION    | VERIFY           | FINANCE_VERIFICATION    | Manager          |
| MANAGER_VERIFICATION    | REQUEST_REVISION | REVISION                | Manager          |
| MANAGER_VERIFICATION    | REJECT           | REJECTED                | Manager          |
| FINANCE_VERIFICATION    | VERIFY           | DBM_APPROVAL            | Finance          |
| FINANCE_VERIFICATION    | REQUEST_REVISION | REVISION                | Finance          |
| FINANCE_VERIFICATION    | REJECT           | REJECTED                | Finance          |
| DBM_APPROVAL            | APPROVE          | BM_APPROVAL             | DBM              |
| DBM_APPROVAL            | REQUEST_REVISION | REVISION                | DBM              |
| DBM_APPROVAL            | REJECT           | REJECTED                | DBM              |
| BM_APPROVAL             | APPROVE          | APPROVED                | BM               |
| BM_APPROVAL             | REQUEST_REVISION | REVISION                | BM               |
| BM_APPROVAL             | REJECT           | REJECTED                | BM               |
| APPROVED                | START_PAYMENT    | PAYMENT_PROCESS         | Finance          |
| PAYMENT_PROCESS         | PAYMENT_SUCCESS  | PAID                    | Finance          |
| DRAFT                   | CANCEL           | CANCELLED               | Owner/Authorized |

---

# 6. Revision Model

Revision harus menjadi workflow event.

Recommended:

```text
REQUEST_REVISION
```

menyimpan:

```text
revision_id
claim_id
requested_by
requested_at
workflow_stage
reason
notes
target_state
resolved_at
resolved_by
```

Contoh:

```text
SUPERVISOR_VERIFICATION
        │
        ▼
REQUEST_REVISION
        │
        ▼
REVISION
        │
        ▼
Requester updates claim
        │
        ▼
RESUBMIT
        │
        ▼
SUPERVISOR_VERIFICATION
```

---

# 7. Revision Target State

Revision tidak selalu harus kembali ke DRAFT.

Target state harus ditentukan berdasarkan stage dan policy.

Contoh:

```text
EDP_REVIEW
    → DRAFT

SUPERVISOR_VERIFICATION
    → EDP_REVIEW

MANAGER_VERIFICATION
    → SUPERVISOR_VERIFICATION

FINANCE_VERIFICATION
    → MANAGER_VERIFICATION

DBM_APPROVAL
    → FINANCE_VERIFICATION

BM_APPROVAL
    → DBM_APPROVAL
```

Policy dapat menentukan apakah revision harus mengulang seluruh stage setelah perubahan.

---

# 8. Rejection Model

Reject harus menyimpan:

```text
rejection_id
claim_id
stage
actor
reason_code
notes
timestamp
```

Contoh reason:

```text
INVALID_DOCUMENT
INVALID_AMOUNT
NOT_ELIGIBLE
DUPLICATE_CLAIM
POLICY_VIOLATION
INSUFFICIENT_EVIDENCE
OTHER
```

---

# 9. Approval Policy

Default:

```text
EDP Verification
    ↓
DBM
    ↓
BM
```

Namun workflow engine harus mendukung:

### Policy A — DBM AND BM

```text
DBM APPROVE
    ↓
BM APPROVE
```

### Policy B — DBM OR BM

```text
       ┌── DBM APPROVE ──┐
Finance ┤                 ├── APPROVED
       └── BM APPROVE ───┘
```

Policy disimpan sebagai konfigurasi, bukan hard-coded.

---

# 10. Workflow Action

Setiap action harus menghasilkan workflow action record.

Minimum:

```text
action_id
claim_id
workflow_instance_id
from_state
action
to_state
actor_id
actor_role
reason
notes
created_at
metadata
```

Contoh:

```text
claim_id: 1001
from_state: SUPERVISOR_VERIFICATION
action: VERIFY
to_state: MANAGER_VERIFICATION
actor_role: EDP_SUPERVISOR
created_at: ...
```

---

# 11. State History

Status history berbeda dari audit trail.

### Status History

Fokus pada lifecycle:

```text
from_state
to_state
actor
timestamp
reason
```

### Audit Trail

Fokus pada aktivitas:

```text
actor
action
entity
entity_id
IP
user-agent
metadata
timestamp
```

Keduanya tetap diperlukan.

---

# 12. Concurrency Control

Workflow transition harus atomic.

Contoh:

Dua Supervisor membuka claim yang sama.

```text
Request A → VERIFY
Request B → VERIFY
```

Tidak boleh menghasilkan dua transition sukses.

Sistem harus memastikan:

```text
current_state masih sesuai expected_state
```

sebelum transition.

Recommended pattern:

```text
BEGIN TRANSACTION

LOCK claim

CHECK current_state

CHECK actor authorization

VALIDATE transition

CREATE workflow_action

UPDATE claim state

CREATE status_history

CREATE audit_event

COMMIT
```

Jika state sudah berubah:

```text
409 Conflict
```

---

# 13. Idempotency

Action penting seperti:

```text
SUBMIT
VERIFY
APPROVE
PAYMENT
```

harus mempertimbangkan idempotency.

Terutama:

```text
PAYMENT
```

Tidak boleh dua request menghasilkan dua pembayaran.

---

# 14. Payment State Machine

Payment memiliki lifecycle sendiri:

```text
NOT_READY
   ↓
READY
   ↓
PROCESSING
   ↓
PAID
```

Failure:

```text
PROCESSING
    ↓
FAILED
```

Retry hanya boleh dilakukan dengan mekanisme idempotent.

---

# 15. Payment Invariants

Sistem harus menjamin:

```text
Payment Amount <= Approved Amount
```

dan:

```text
Paid Claim ≠ Paid Again
```

serta:

```text
Payment hanya untuk APPROVED claim
```

---

# 16. SLA Clock

SLA tidak boleh dihitung hanya berdasarkan `created_at`.

Setiap stage memiliki:

```text
stage_started_at
stage_completed_at
```

Contoh:

```text
EDP Review
started  : 2026-10-01 09:00
completed: 2026-10-02 11:00
```

Durasi dihitung menggunakan working calendar.

---

# 17. SLA Status

```text
ON_TIME
AT_RISK
BREACHED
```

Contoh policy:

```text
0–80% SLA → ON_TIME
80–100%   → AT_RISK
>100%     → BREACHED
```

Threshold harus configurable.

---

# 18. SLA History

Sistem harus dapat menghasilkan:

```text
Average Processing Time
Average Review Time
Average Verification Time
Average Approval Time
Average Payment Time
```

Dimensi:

* periode;
* claim type;
* department;
* role;
* stage;
* user.

---

# 19. Notification Workflow

Notification dipicu oleh workflow event.

Contoh:

```text
SUBMITTED
   ↓
Telegram notification

REQUEST_REVISION
   ↓
Telegram notification

VERIFY
   ↓
Telegram notification

APPROVE
   ↓
Telegram notification

PAID
   ↓
Telegram notification
```

Notification failure tidak boleh rollback transaction utama.

---

# 20. Workflow Invariants

## INV-WF-001

Tidak boleh transition dari state yang salah.

```text
expected_state != current_state
→ reject
```

---

## INV-WF-002

Tidak boleh approve tanpa verification yang dipersyaratkan.

---

## INV-WF-003

Tidak boleh payment tanpa final approval.

---

## INV-WF-004

Tidak boleh payment lebih dari sekali.

---

## INV-WF-005

Setiap transition harus memiliki actor.

---

## INV-WF-006

Setiap rejection harus memiliki reason.

---

## INV-WF-007

Setiap revision harus memiliki reason.

---

## INV-WF-008

History tidak boleh dihapus.

---

## INV-WF-009

Workflow action harus atomic dengan state update.

---

## INV-WF-010

Authorization harus dilakukan server-side.

---

# 21. Workflow Error Codes

Recommended:

```text
WF_INVALID_STATE
WF_INVALID_TRANSITION
WF_UNAUTHORIZED
WF_FORBIDDEN
WF_REVISION_REASON_REQUIRED
WF_REJECTION_REASON_REQUIRED
WF_APPROVAL_REQUIRED
WF_PAYMENT_NOT_ALLOWED
WF_ALREADY_PAID
WF_CONCURRENT_UPDATE
WF_IDEMPOTENCY_CONFLICT
```

---

# 22. Workflow API Concept

Contoh endpoint konseptual:

```http
POST /api/claims
POST /api/claims/{claim}/submit

POST /api/claims/{claim}/review
POST /api/claims/{claim}/verify
POST /api/claims/{claim}/request-revision
POST /api/claims/{claim}/reject
POST /api/claims/{claim}/approve

POST /api/claims/{claim}/payment
```

Namun endpoint final harus mengikuti desain API specification dan tidak boleh mengabaikan state machine.

---

# 23. Workflow UI Principle

Frontend harus menampilkan action berdasarkan:

```text
Current User
+
Permission
+
Current State
+
Workflow Policy
```

Contoh:

```text
BM
```

hanya melihat tombol:

```text
APPROVE
REQUEST REVISION
REJECT
```

ketika claim memang berada pada:

```text
BM_APPROVAL
```

Frontend menyembunyikan tombol yang tidak relevan untuk UX, tetapi backend tetap wajib melakukan authorization.

---

# 24. Claim Timeline

UI sebaiknya menyediakan timeline:

```text
01 Oct 09:00
Claim Created
        │
01 Oct 10:00
Submitted
        │
01 Oct 13:00
EDP Review
        │
02 Oct 09:00
Supervisor Verified
        │
02 Oct 14:00
Manager Verified
        │
03 Oct 09:00
Finance Verified
        │
03 Oct 13:00
DBM Approved
        │
04 Oct 09:00
BM Approved
        │
04 Oct 15:00
Paid
```

Timeline berasal dari persisted workflow/status history.

---

# 25. Workflow Configuration

Workflow configuration minimal dapat mencakup:

```text
workflow_name
workflow_version
active
approval_policy
stages
stage_order
allowed_roles
allowed_actions
revision_policy
sla_policy
```

Workflow versioning diperlukan agar perubahan workflow tidak mengubah interpretasi histori lama.

---

# 26. Future Extensibility

Workflow harus dapat menambahkan:

* additional verifier;
* additional approver;
* regional approval;
* amount-based approval;
* department approval;
* emergency approval;
* parallel approval;
* conditional approval.

Contoh:

```text
Claim <= Rp1.000.000
    → DBM

Claim > Rp1.000.000
    → DBM
    → BM
```

Atau:

```text
Health Claim
    → Health Verification
    → Finance
```

Tanpa mengubah fundamental model claim.

---

# 27. Recommended Architecture

Konsep:

```text
                ┌────────────────────┐
                │       CLAIM        │
                │ current_state      │
                └─────────┬──────────┘
                          │
                          ▼
                ┌────────────────────┐
                │ WORKFLOW INSTANCE  │
                └─────────┬──────────┘
                          │
                          ▼
                ┌────────────────────┐
                │ WORKFLOW ACTION    │
                └─────────┬──────────┘
                          │
             ┌────────────┴────────────┐
             ▼                         ▼
     STATUS HISTORY              AUDIT TRAIL
             │
             ▼
        SLA ANALYTICS
```

---

# 28. Final Workflow

Default production workflow:

```text
┌──────────┐
│  DRAFT   │
└────┬─────┘
     │ SUBMIT
     ▼
┌────────────┐
│ SUBMITTED  │
└─────┬──────┘
      ▼
┌─────────────────┐
│   EDP REVIEW    │
└───────┬─────────┘
        │ FORWARD
        ▼
┌────────────────────────┐
│ SUPERVISOR VERIFICATION│
└───────────┬────────────┘
            │ VERIFY
            ▼
┌─────────────────────┐
│ MANAGER VERIFICATION│
└──────────┬──────────┘
           │ VERIFY
           ▼
┌────────────────────┐
│ FINANCE VERIFICATION│
└──────────┬─────────┘
           │ VERIFY
           ▼
┌──────────────┐
│ DBM APPROVAL │
└──────┬───────┘
       │ APPROVE
       ▼
┌──────────────┐
│ BM APPROVAL  │
└──────┬───────┘
       │ APPROVE
       ▼
┌──────────────┐
│   APPROVED   │
└──────┬───────┘
       │ START PAYMENT
       ▼
┌──────────────────┐
│ PAYMENT PROCESS  │
└────────┬─────────┘
         │ PAYMENT SUCCESS
         ▼
┌──────────┐
│   PAID   │
└──────────┘
```

Dengan branch:

```text
REVISION
REJECTION
CANCELLATION
```

dan semua transition:

```text
AUTHORIZED
+
VALIDATED
+
ATOMIC
+
AUDITED
```

---

# 29. Definition of Done — Workflow

Workflow dianggap selesai apabila:

* [ ] seluruh state didefinisikan;
* [ ] seluruh transition didefinisikan;
* [ ] actor setiap transition didefinisikan;
* [ ] permission setiap transition didefinisikan;
* [ ] revision policy didefinisikan;
* [ ] rejection policy didefinisikan;
* [ ] approval policy didefinisikan;
* [ ] payment lifecycle didefinisikan;
* [ ] SLA lifecycle didefinisikan;
* [ ] concurrency protection didefinisikan;
* [ ] idempotency didefinisikan;
* [ ] audit trail didefinisikan;
* [ ] state history didefinisikan;
* [ ] workflow versioning didefinisikan;
* [ ] database model siap diturunkan dari workflow;
* [ ] API contract siap diturunkan dari workflow;
* [ ] UI action dapat diturunkan dari state + permission.

---

**End of WORKFLOW.md**
