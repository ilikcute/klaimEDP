# DOMAIN MODEL

## Sistem Rekap, Tracking & Analisa Klaim / Reimbursement EDP

**Version:** 1.0
**Status:** Architecture Baseline

---

# 1. Tujuan

Domain Model mendefinisikan objek bisnis utama, hubungan antar-entitas, aturan ownership, lifecycle, dan invariant sistem.

Domain model menjadi dasar untuk:

* database schema;
* service layer;
* API;
* authorization;
* workflow;
* reporting;
* audit.

---

# 2. Core Domain

Core domain sistem adalah:

```text
CLAIM / REIMBURSEMENT
```

Claim memiliki lifecycle:

```text
Claim
 ├── Receipts
 ├── Documents
 ├── Workflow
 ├── Status History
 ├── Audit Trail
 ├── Payment
 └── Analytics
```

---

# 3. Aggregate Root

`Claim` merupakan **Aggregate Root**.

Entitas yang berada dalam aggregate claim:

```text
Claim
 ├── ClaimReceipt
 ├── ClaimDocument
 ├── WorkflowInstance
 ├── WorkflowAction
 ├── ClaimStatusHistory
 └── Payment
```

Perubahan penting terhadap child entity harus dikontrol melalui business service dan authorization.

---

# 4. User

```text
User
 ├── id
 ├── name
 ├── username/email
 ├── status
 └── roles
```

User dapat:

* membuat claim;
* menjadi requester;
* melakukan review;
* melakukan verification;
* melakukan approval;
* melakukan payment;

sesuai role dan permission.

---

# 5. Claim

Representasi satu permintaan reimbursement.

Konsep:

```text
Claim
 ├── claim_number
 ├── requester
 ├── claim_type
 ├── claim_date
 ├── description
 ├── total_amount
 ├── current_state
 └── workflow_instance
```

Invariant:

```text
claim.total_amount
=
SUM(claim_receipts.amount)
```

`total_amount` tidak boleh menjadi sumber kebenaran yang independen dari receipt.

---

# 6. Claim Type

Enum/domain reference:

```text
BBM
TOL
HEALTH
```

Claim type menentukan business rules.

### BBM

Memerlukan:

* vehicle;
* distance;
* fuel liter;
* applicable standard.

### TOL

Memerlukan:

* receipt;
* transaction information.

Tidak ada perhitungan KM/L.

### HEALTH

Memerlukan:

* medical expense information;
* annual utilization check.

---

# 7. Claim Receipt

Satu claim dapat mempunyai banyak receipt.

```text
Claim 1
 ├── Receipt 1
 ├── Receipt 2
 ├── Receipt 3
 └── Receipt N
```

Semua receipt wajib memiliki requester/claim owner yang sama melalui claim.

Data konseptual:

```text
ClaimReceipt
 ├── id
 ├── claim_id
 ├── receipt_number
 ├── transaction_date
 ├── merchant
 ├── amount
 ├── description
 └── metadata
```

---

# 8. Claim Document

Dokumen/evidence yang berhubungan dengan claim atau receipt.

```text
Claim
 ├── Document A
 ├── Document B
 └── Document C
```

Jenis:

```text
RECEIPT
SUPPORTING_DOCUMENT
PHYSICAL_DOCUMENT_REFERENCE
PAYMENT_PROOF
OTHER
```

Status:

```text
PENDING
RECEIVED
VERIFIED
REJECTED
ARCHIVED
```

File aktual disimpan pada private storage.

Database menyimpan metadata.

---

# 9. Vehicle

Vehicle diperlukan untuk BBM.

```text
Vehicle
 ├── id
 ├── type
 ├── plate_number
 ├── description
 └── active
```

Vehicle type minimal:

```text
MOTOR
CAR
```

---

# 10. BBM Standard

Standard konsumsi harus menjadi entitas tersendiri karena nilainya dapat berubah.

Default:

```text
MOTOR = 40 KM/L
CAR   = 13 KM/L
```

Konsep:

```text
BbmStandard
 ├── vehicle_type
 ├── standard_km_per_liter
 ├── effective_from
 ├── effective_until
 └── active
```

Historical claim menggunakan standard berdasarkan effective date.

---

# 11. BBM Calculation

Input:

```text
distance_km
fuel_liter
```

Formula:

```text
actual_km_per_liter
=
distance_km / fuel_liter
```

Comparison:

```text
actual
vs
standard
```

Output:

```text
COMPLIANT
BELOW_STANDARD
ABOVE_STANDARD
```

Tolerance dapat dikonfigurasi.

---

# 12. Health Policy

Health policy menyimpan limit tahunan.

Default:

```text
Rp10.000.000 / user / year
```

Concept:

```text
HealthPolicy
 ├── annual_limit
 ├── effective_from
 └── effective_until
```

Historical claim harus menggunakan policy yang berlaku saat claim.

---

# 13. Health Utilization

Konsep:

```text
Annual Limit
-
Paid Amount
=
Remaining Limit
```

Pending/approved dapat ditampilkan sebagai exposure tetapi tidak menggantikan paid utilization sebagai basis final utilization, kecuali policy diubah.

---

# 14. Workflow Instance

Satu claim memiliki workflow instance.

```text
Claim
  │
  └── WorkflowInstance
        ├── version
        ├── current_state
        └── actions
```

Workflow instance memungkinkan workflow berubah tanpa mengubah histori workflow lama.

---

# 15. Workflow Action

Setiap transition menghasilkan action.

```text
WorkflowAction
 ├── from_state
 ├── action
 ├── to_state
 ├── actor
 ├── reason
 ├── notes
 └── created_at
```

Contoh:

```text
SUPERVISOR_VERIFICATION
       ↓
VERIFY
       ↓
MANAGER_VERIFICATION
```

---

# 16. Claim Status History

Status history menyimpan lifecycle claim.

```text
ClaimStatusHistory
 ├── claim_id
 ├── from_state
 ├── to_state
 ├── actor_id
 └── timestamp
```

Tidak boleh dihapus.

---

# 17. Payment

Payment merupakan entity tersendiri.

```text
Payment
 ├── claim_id
 ├── amount
 ├── payment_date
 ├── bank
 ├── account_number
 ├── account_name
 ├── reference_number
 ├── proof_document
 └── processed_by
```

Invariant:

```text
payment.amount <= approved_amount
```

Satu claim normalnya hanya memiliki satu successful payment.

---

# 18. SLA

SLA terdiri dari:

```text
SlaPolicy
SlaInstance
```

Policy:

```text
duration
working_calendar
at_risk_threshold
```

Instance:

```text
claim_id
stage
started_at
due_at
completed_at
status
```

---

# 19. Audit Event

Audit trail bersifat append-only.

```text
AuditEvent
 ├── actor
 ├── action
 ├── entity
 ├── entity_id
 ├── IP
 ├── user_agent
 ├── metadata
 └── timestamp
```

---

# 20. Notification

Notification merupakan delivery concern.

```text
Notification
 ├── recipient
 ├── event
 ├── channel
 ├── status
 ├── sent_at
 └── failure_reason
```

Telegram bukan source of truth.

---

# 21. Entity Relationship

Konsep:

```text
USER
 │
 │ 1:N
 ▼
CLAIM
 │
 ├────────────── 1:N ── CLAIM_RECEIPT
 │
 ├────────────── 1:N ── CLAIM_DOCUMENT
 │
 ├────────────── 1:1 ── WORKFLOW_INSTANCE
 │                         │
 │                         └── 1:N WORKFLOW_ACTION
 │
 ├────────────── 1:N ── STATUS_HISTORY
 │
 ├────────────── 1:1 ── PAYMENT
 │
 └────────────── 1:N ── SLA_INSTANCE
```

---

# 22. Domain Invariants

### INV-001

Claim harus memiliki requester.

### INV-002

Claim harus memiliki claim type.

### INV-003

Claim submitted harus memiliki receipt.

### INV-004

Total claim berasal dari receipt.

### INV-005

Semua receipt dalam claim berasal dari requester yang sama.

### INV-006

Workflow transition harus valid.

### INV-007

Workflow transition harus authorized.

### INV-008

Payment hanya setelah final approval.

### INV-009

Payment tidak boleh double.

### INV-010

History tidak boleh dihapus.

### INV-011

BBM standard harus historical/effective-dated.

### INV-012

Health limit dihitung per user/year.

### INV-013

DBM atau BM **wajib memberikan satu management approval**.

### INV-014

DBM dan BM tidak harus keduanya approve.

### INV-015

Notification failure tidak boleh membatalkan business transaction.

---

# 23. Management Approval Gate

Final management approval:

```text
FINANCE_VERIFICATION
        │
        ▼
MANAGEMENT_APPROVAL
        │
        ├── DBM APPROVE ──┐
        │                 │
        └── BM APPROVE ──┤
                          ▼
                       APPROVED
```

Rule:

```text
DBM OR BM
```

Minimal satu harus approve.

Jika salah satu sudah approve, gate selesai.

---

# 24. Separation of Duties

Recommended:

```text
Requester
    ≠
Verifier
    ≠
Management Approver
    ≠
Payer
```

Pengecualian hanya berdasarkan policy organisasi dan seluruh exception diaudit.

---

# 25. Design Principle

Domain model harus mendukung:

* workflow versioning;
* policy versioning;
* historical accuracy;
* auditability;
* concurrency safety;
* idempotency;
* future approval stages;
* future claim types.

---

**End of DOMAIN_MODEL.md**
