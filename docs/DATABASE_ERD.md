# DATABASE ERD

# Sistem Rekap, Tracking & Analisa Klaim / Reimbursement EDP

**Version:** 1.1
**Status:** Architecture Baseline
**Database:** MySQL 8.x
**Backend:** Laravel
**Authorization Model:** Role + Permission + Policy

---

# 1. ERD Architecture Overview

Database dibagi menjadi beberapa domain:

```text
┌───────────────────────────────────────────────────────────────┐
│                    IDENTITY & ACCESS                          │
│                                                               │
│ USERS ── ROLE_USER ── ROLES ── PERMISSION_ROLE ── PERMISSIONS│
└───────────────────────────────┬───────────────────────────────┘
                                │
                                ▼
┌───────────────────────────────────────────────────────────────┐
│                         CLAIM DOMAIN                          │
│                                                               │
│ CLAIMS ── CLAIM_RECEIPTS                                     │
│    │                                                          │
│    ├── CLAIM_DOCUMENTS                                        │
│    ├── CLAIM_BBM_DETAILS ── VEHICLES                          │
│    └── CLAIM_HEALTH_DETAILS ── HEALTH_POLICIES                │
└───────────────────────────────┬───────────────────────────────┘
                                │
                                ▼
┌───────────────────────────────────────────────────────────────┐
│                        WORKFLOW DOMAIN                        │
│                                                               │
│ WORKFLOW_DEFINITIONS                                          │
│        │                                                      │
│        ├── WORKFLOW_STAGES                                     │
│        ├── WORKFLOW_TRANSITIONS                               │
│        └── WORKFLOW_APPROVAL_POLICIES                         │
│                                                               │
│ CLAIMS ── WORKFLOW_INSTANCES ── WORKFLOW_ACTIONS              │
└───────────────────────────────┬───────────────────────────────┘
                                │
                                ▼
┌───────────────────────────────────────────────────────────────┐
│                         FINANCE DOMAIN                         │
│                                                               │
│ CLAIMS ── PAYMENTS                                             │
└───────────────────────────────┬───────────────────────────────┘
                                │
                                ▼
┌───────────────────────────────────────────────────────────────┐
│                     SLA / AUDIT / NOTIFICATION                 │
│                                                               │
│ SLA_POLICIES ── SLA_INSTANCES                                 │
│ CLAIMS ── STATUS_HISTORIES                                    │
│ USERS ── AUDIT_EVENTS                                         │
│ USERS ── NOTIFICATIONS                                        │
│ OUTBOX_EVENTS ── QUEUE / TELEGRAM                             │
└───────────────────────────────────────────────────────────────┘
```

---

# 2. Identity & Access ERD

```text
USERS
  │
  │ N:M
  ▼
ROLE_USER
  │
  ▼
ROLES
  │
  │ N:M
  ▼
PERMISSION_ROLE
  │
  ▼
PERMISSIONS
```

Relasi:

```text
users 1:N role_user
roles 1:N role_user

roles 1:N permission_role
permissions 1:N permission_role
```

Model ini memungkinkan seorang user memiliki lebih dari satu role jika kebutuhan bisnis di masa depan mengharuskannya.

---

# 3. Authorization Architecture

Authorization terdiri dari tiga lapisan:

```text
USER
 │
 ▼
ROLE
 │
 ▼
PERMISSION
 │
 ▼
POLICY
```

Contoh:

```text
User:
Budi

Role:
BM

Permission:
claim.approve

Approval Policy:
Management Approval
ANY_OF
DBM/BM
minimum = 1
```

Keputusan akhir:

```text
Role eligible
+
Permission granted
+
Policy matched
+
Workflow state valid
=
APPROVAL ALLOWED
```

---

# 4. Claim Core ERD

```text
USERS
  │
  │ 1:N
  ▼
CLAIMS
  │
  ├──────── 1:N ──────── CLAIM_RECEIPTS
  │
  ├──────── 1:N ──────── CLAIM_DOCUMENTS
  │
  ├──────── 1:1 ──────── CLAIM_BBM_DETAILS
  │
  ├──────── 1:1 ──────── CLAIM_HEALTH_DETAILS
  │
  ├──────── 1:1 ──────── WORKFLOW_INSTANCES
  │
  ├──────── 1:N ──────── WORKFLOW_ACTIONS
  │
  ├──────── 1:N ──────── CLAIM_STATUS_HISTORIES
  │
  ├──────── 1:N ──────── SLA_INSTANCES
  │
  └──────── 1:1 ──────── PAYMENTS
```

---

# 5. CLAIMS

```text
claims
-----------------------------------------
id PK
claim_number UNIQUE
requester_id FK → users.id
claim_type
claim_date
description
total_amount
current_state
submitted_at
approved_at
paid_at
created_at
updated_at
deleted_at NULL
```

Catatan:

`workflow_instance_id` **tidak lagi disimpan di `claims`**.

Relasi workflow ditentukan melalui:

```text
workflow_instances.claim_id UNIQUE
```

Hal ini menghindari circular/redundant relationship.

---

# 6. CLAIM_RECEIPTS

```text
claim_receipts
-----------------------------------------
id PK
claim_id FK → claims.id
receipt_number NULL
transaction_date
merchant
amount
description
created_at
updated_at
```

Relationship:

```text
CLAIM 1 ───────── N CLAIM_RECEIPTS
```

Business invariant:

```text
SUM(receipts.amount) = claims.total_amount
```

`claims.total_amount` merupakan cached/derived value untuk kebutuhan query cepat, tetapi sumber kebenaran nominal adalah receipt.

---

# 7. CLAIM_DOCUMENTS

```text
claim_documents
-----------------------------------------
id PK
claim_id FK → claims.id
claim_receipt_id FK NULL
document_type
status
original_filename
storage_disk
storage_path
mime_type
file_size
checksum_sha256
uploaded_by FK → users.id
received_at NULL
verified_at NULL
created_at
updated_at
```

Relationship:

```text
CLAIM 1 ───── N DOCUMENT
RECEIPT 1 ─── N DOCUMENT
USER 1 ─────── N DOCUMENT
```

Dokumen dapat berupa:

```text
nota
struk
scan
foto
PDF
dokumen fisik yang kemudian dicatat
bukti pembayaran
dokumen pendukung
```

---

# 8. BBM ERD

```text
VEHICLES
   │
   │ 1:N
   ▼
CLAIM_BBM_DETAILS
   │
   │ 1:1
   ▼
CLAIMS

BBM_STANDARDS
   │
   │ effective-date lookup
   ▼
CLAIM_BBM_DETAILS
```

`claim_bbm_details` menyimpan snapshot:

```text
vehicle_type
standard_km_per_liter
distance_km
fuel_liter
actual_km_per_liter
variance
```

---

# 9. BBM Standard

```text
bbm_standards
-----------------------------------------
id PK
vehicle_type
standard_km_per_liter
effective_from
effective_until NULL
is_active
created_at
updated_at
```

Default business standard:

```text
MOTOR = 40 KM/L
MOBIL = 13 KM/L
```

Namun angka tersebut harus diperlakukan sebagai policy/configuration, bukan hardcoded di frontend.

---

# 10. HEALTH ERD

```text
HEALTH_POLICIES
       │
       │ effective-date lookup
       ▼
CLAIM_HEALTH_DETAILS
       │
       │ 1:1
       ▼
     CLAIMS
```

Health detail menyimpan:

```text
policy_id
policy_limit_snapshot
```

Tujuannya menjaga historical truth.

Jika limit berubah:

```text
2026 = Rp10.000.000
2027 = Rp12.000.000
```

claim 2026 tidak ikut berubah menjadi Rp12 juta.

---

# 11. Workflow Configuration ERD

```text
WORKFLOW_DEFINITIONS
        │
        │ 1:N
        ▼
WORKFLOW_STAGES
        │
        │ 1:N
        ▼
WORKFLOW_TRANSITIONS
```

Contoh:

```text
REIMBURSEMENT_V1
       │
       ├── EDP_REVIEW
       ├── SUPERVISOR_VERIFICATION
       ├── MANAGER_VERIFICATION
       ├── FINANCE_VERIFICATION
       ├── MANAGEMENT_APPROVAL
       └── PAYMENT_PROCESS
```

---

# 12. Workflow Instance

```text
CLAIMS
  │
  │ 1:1
  ▼
WORKFLOW_INSTANCES
  │
  │ 1:N
  ▼
WORKFLOW_ACTIONS
```

`workflow_instances`:

```text
id PK
claim_id FK UNIQUE
workflow_definition_id FK
workflow_version
started_at
completed_at NULL
created_at
updated_at
```

Constraint:

```text
UNIQUE(claim_id)
```

---

# 13. Workflow Actions

```text
workflow_actions
-----------------------------------------
id PK
claim_id FK
workflow_instance_id FK
transition_id FK NULL
from_state
action
to_state
actor_id FK
actor_role
reason NULL
notes NULL
approval_policy_id FK NULL
approval_policy_version NULL
created_at
```

Workflow action menjadi historical record utama untuk:

```text
SUBMIT
REVIEW
VERIFY
APPROVE
REQUEST_REVISION
REJECT
PROCESS_PAYMENT
COMPLETE_PAYMENT
```

---

# 14. Approval Policy ERD

Bagian utama architecture:

```text
WORKFLOW_STAGES
       │
       ▼
APPROVAL_POLICIES
       │
       ├── APPROVAL_POLICY_ROLES
       │             │
       │             ▼
       │           ROLES
       │
       └── APPROVAL_POLICY_SCOPES
```

Tujuannya memisahkan:

```text
Role
Permission
Approval Policy
```

---

# 15. Approval Policy

```text
approval_policies
-----------------------------------------
id PK
workflow_stage_id FK
name
version
approval_rule
minimum_approvals
priority
min_amount NULL
max_amount NULL
claim_type NULL
effective_from
effective_until NULL
is_active
created_at
updated_at
```

Contoh:

```text
name:
Management Approval

version:
3

approval_rule:
ANY_OF

minimum_approvals:
1

claim_type:
NULL

min_amount:
NULL

max_amount:
NULL
```

---

# 16. Approval Policy Roles

```text
approval_policies
       │
       │ 1:N
       ▼
approval_policy_roles
       │
       ▼
roles
```

Table:

```text
approval_policy_roles
-----------------------------------------
id PK
approval_policy_id FK
role_id FK
created_at
```

Contoh:

```text
Management Approval V3

DBM
BM
```

Sehingga:

```text
eligible_roles = DBM,BM
```

tidak perlu disimpan sebagai string:

```text
"DBM,BM"
```

Tetapi menggunakan relational table.

---

# 17. Approval Policy Scope

Untuk kebutuhan future-proof:

```text
approval_policy_scopes
-----------------------------------------
id PK
approval_policy_id FK
scope_type
scope_value
created_at
```

Contoh scope:

```text
DEPARTMENT
BRANCH
REGION
CLAIM_TYPE
REQUESTER_GROUP
```

Namun hanya scope yang benar-benar diperlukan yang perlu digunakan.

Jangan membuat policy engine terlalu kompleks pada V1.

---

# 18. Management Approval

Business rule:

```text
MANAGEMENT_APPROVAL
```

Policy:

```text
approval_rule = ANY_OF
minimum_approvals = 1
eligible_roles = DBM, BM
```

Flow:

```text
                  MANAGEMENT_APPROVAL
                           │
                           ▼
                    Approval Policy
                           │
                 ┌─────────┴─────────┐
                 │                   │
                DBM                 BM
                 │                   │
                 └─────────┬─────────┘
                           ▼
                     ONE APPROVAL
                           │
                           ▼
                       APPROVED
```

---

# 19. Approval Decision

Untuk auditability, setiap approval action menyimpan:

```text
approval_policy_id
approval_policy_version
actor_id
actor_role
```

Tidak diperlukan:

```text
approved_by_dbm
approved_by_bm
```

---

# 20. Approval Decision History

Workflow action menjadi decision history:

```text
CLAIM
 │
 ▼
WORKFLOW_ACTION
 │
 ├── actor_id
 ├── actor_role
 ├── action
 ├── from_state
 ├── to_state
 ├── approval_policy_id
 └── approval_policy_version
```

Contoh:

```text
Claim:
EDP-2026-000123

Actor:
User #87

Role:
BM

Action:
APPROVE

From:
MANAGEMENT_APPROVAL

To:
APPROVED

Policy:
Management Approval V3
```

---

# 21. Status History

```text
CLAIMS
  │
  │ 1:N
  ▼
CLAIM_STATUS_HISTORIES
```

Table:

```text
claim_status_histories
-----------------------------------------
id PK
claim_id FK
from_state NULL
to_state
action
actor_id FK
reason NULL
notes NULL
created_at
```

Status history bersifat:

```text
APPEND ONLY
```

---

# 22. SLA ERD

```text
SLA_POLICIES
      │
      │ 1:N
      ▼
SLA_INSTANCES
      │
      │ N:1
      ▼
CLAIMS
```

SLA instance menyimpan:

```text
stage
started_at
due_at
completed_at
status
duration
```

---

# 23. Payment ERD

```text
CLAIMS
  │
  │ 1:1
  ▼
PAYMENTS
  │
  ├── processed_by → USERS
  │
  └── payment_proof_document_id
                 │
                 ▼
          CLAIM_DOCUMENTS
```

Constraint:

```text
UNIQUE(payments.claim_id)
UNIQUE(payments.idempotency_key)
```

---

# 24. Audit ERD

```text
USERS
  │
  │ 1:N
  ▼
AUDIT_EVENTS
```

Audit event:

```text
id
actor_id
event_type
entity_type
entity_id
before_data
after_data
ip_address
user_agent
created_at
```

Untuk perubahan sensitif, before/after state harus dapat ditelusuri.

---

# 25. Notifications

```text
USERS
  │
  │ 1:N
  ▼
NOTIFICATIONS
```

Claim reference:

```text
reference_type
reference_id
```

---

# 26. Outbox

```text
BUSINESS TRANSACTION
        │
        ▼
OUTBOX_EVENTS
        │
        ▼
QUEUE
        │
        ▼
TELEGRAM
```

Outbox:

```text
id PK
event_type
aggregate_type
aggregate_id
payload
status
attempts
available_at
processed_at
last_error
created_at
updated_at
```

Telegram failure tidak boleh rollback transaksi claim.

---

# 27. Full ERD

```text
                         ┌──────────────┐
                         │    USERS     │
                         └──────┬───────┘
                                │
              ┌─────────────────┼─────────────────┐
              │                 │                 │
              ▼                 ▼                 ▼
         ROLE_USER        CLAIMS            AUDIT_EVENTS
              │              │
              ▼              │
            ROLES             │
              │              │
              ▼              │
     PERMISSION_ROLE          │
              │              │
              ▼              │
        PERMISSIONS          │
                             │
       ┌─────────────────────┼──────────────────────────┐
       │                     │                          │
       ▼                     ▼                          ▼
CLAIM_RECEIPTS       CLAIM_DOCUMENTS              WORKFLOW_INSTANCES
       │                     │                          │
       │                     │                          ▼
       │                     │                  WORKFLOW_ACTIONS
       │                     │                          │
       │                     │                          │
       ▼                     ▼                          ▼
CLAIM_BBM_DETAILS      DOCUMENT LINKS             APPROVAL_POLICY
       │                                                │
       ▼                                                ├── POLICY_ROLES
   VEHICLES                                             │       │
                                                        │       ▼
                                                        │     ROLES
                                                        │
                                                        └── POLICY_SCOPES

CLAIM_HEALTH_DETAILS
       │
       ▼
HEALTH_POLICIES


CLAIMS
  │
  ├── CLAIM_STATUS_HISTORIES
  │
  ├── SLA_INSTANCES ── SLA_POLICIES
  │
  └── PAYMENTS
          │
          └── CLAIM_DOCUMENTS


USERS
  │
  └── NOTIFICATIONS

OUTBOX_EVENTS
  │
  └── TELEGRAM
```

---

# 28. Core Cardinality

| Parent            | Child             | Cardinality |
| ----------------- | ----------------- | ----------: |
| User              | Claims            |         1:N |
| User              | Roles             |         N:M |
| Role              | Permissions       |         N:M |
| Claim             | Receipts          |         1:N |
| Claim             | Documents         |         1:N |
| Claim             | BBM Detail        |         1:1 |
| Claim             | Health Detail     |         1:1 |
| Claim             | Workflow Instance |         1:1 |
| Workflow Instance | Workflow Actions  |         1:N |
| Workflow Stage    | Approval Policies |         1:N |
| Approval Policy   | Roles             |         N:M |
| Claim             | Status History    |         1:N |
| Claim             | SLA Instances     |         1:N |
| Claim             | Payment           |         1:1 |
| User              | Audit Events      |         1:N |
| User              | Notifications     |         1:N |

---

# 29. Critical Constraints

### Approval

```text
approval_rule = ANY_OF
minimum_approvals = 1
eligible_roles = DBM + BM
```

### Payment

```text
claim_id UNIQUE
idempotency_key UNIQUE
```

### Workflow

```text
workflow_instances.claim_id UNIQUE
```

### Claim

```text
claim_number UNIQUE
```

### Audit

```text
APPEND ONLY
```

---

# 30. Index Strategy

### Claims

```text
INDEX(requester_id, claim_date)
INDEX(current_state, created_at)
INDEX(claim_type, claim_date)
INDEX(current_state, updated_at)
```

### Receipts

```text
INDEX(claim_id)
INDEX(receipt_number)
INDEX(transaction_date)
```

### Documents

```text
INDEX(claim_id)
INDEX(claim_receipt_id)
INDEX(checksum_sha256)
```

### Workflow

```text
UNIQUE(claim_id)
INDEX(workflow_definition_id)
INDEX(claim_id, created_at)
```

### Workflow Actions

```text
INDEX(claim_id, created_at)
INDEX(actor_id, created_at)
INDEX(action, created_at)
```

### Approval Policies

```text
INDEX(workflow_stage_id, is_active)
INDEX(effective_from, effective_until)
INDEX(priority)
```

### Approval Policy Roles

```text
UNIQUE(approval_policy_id, role_id)
INDEX(role_id, approval_policy_id)
```

### SLA

```text
INDEX(claim_id, status)
INDEX(status, due_at)
```

### Payments

```text
UNIQUE(claim_id)
UNIQUE(idempotency_key)
INDEX(status, payment_date)
```

### Audit

```text
INDEX(actor_id, created_at)
INDEX(entity_type, entity_id, created_at)
INDEX(event_type, created_at)
```

---

# 31. Delete Strategy

Transactional data:

```text
RESTRICT
```

Audit:

```text
APPEND ONLY
```

Documents:

```text
Logical lifecycle
```

Submitted claims:

```text
NO HARD DELETE
```

Reference/master data:

```text
Prefer soft delete / inactive flag
```

---

# 32. Important Architecture Decision

Database **tidak** menyimpan:

```text
approved_by_dbm
approved_at_dbm
approved_by_bm
approved_at_bm
```

Database menyimpan:

```text
workflow_actions
```

dengan:

```text
actor_id
actor_role
approval_policy_id
approval_policy_version
action
from_state
to_state
```

Dengan demikian database tetap fleksibel jika suatu hari approval berubah menjadi:

```text
DBM OR BM
```

atau:

```text
DBM AND Finance
```

atau:

```text
2 OF 3
```

tanpa mengubah struktur `claims`.

---

# 33. Policy Resolution Flow

```text
USER
 │
 ▼
ROLE
 │
 ▼
PERMISSION
 │
 ▼
CLAIM
 │
 ▼
CURRENT WORKFLOW STAGE
 │
 ▼
FIND ACTIVE POLICY
 │
 ├── claim_type
 ├── amount
 ├── organizational scope
 └── effective date
 │
 ▼
ELIGIBLE ROLES
 │
 ▼
USER ELIGIBILITY
 │
 ▼
APPROVAL DECISION
 │
 ▼
WORKFLOW ACTION
```

---

# 34. Definition of Done

* [x] Identity model tersedia.
* [x] Role model tersedia.
* [x] Permission model tersedia.
* [x] Claim model tersedia.
* [x] Receipt model tersedia.
* [x] Document model tersedia.
* [x] BBM model tersedia.
* [x] Health model tersedia.
* [x] Workflow configuration tersedia.
* [x] Workflow instance tersedia.
* [x] Workflow action tersedia.
* [x] Approval policy tersedia.
* [x] Policy-role relationship tersedia.
* [x] Policy versioning tersedia.
* [x] Effective dating tersedia.
* [x] Management approval menggunakan DBM OR BM.
* [x] Minimum approval = 1.
* [x] Tidak ada hardcoded `approved_by_dbm` / `approved_by_bm`.
* [x] Payment 1:1.
* [x] Payment idempotency tersedia.
* [x] Status history append-only.
* [x] Audit append-only.
* [x] Outbox tersedia.
* [x] Index strategy tersedia.
* [x] Workflow instance tidak memiliki relationship redundant pada claims.

---

# 35. Final Architecture Principle

> **Database menyimpan fakta dan historical decisions. Role menentukan kapabilitas dasar. Permission menentukan operasi yang boleh dilakukan. Approval Policy menentukan eligibility berdasarkan kondisi bisnis. Workflow menentukan apakah action valid pada state tertentu.**

**End of DATABASE_ERD.md**
