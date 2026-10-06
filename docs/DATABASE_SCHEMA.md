# DATABASE_SCHEMA.md

# Sistem Rekap, Tracking & Analisa Klaim / Reimbursement EDP

**Version:** 1.0
**Status:** Implementation Baseline
**Database:** MySQL 8.x / InnoDB
**Framework:** Laravel
**Authorization:** Role + Permission + Policy

---

# 1. Database Design Principles

Database menggunakan prinsip:

```text
InnoDB
UTF-8 / utf8mb4
Foreign Key
Transaction
Row Locking
Append-only Audit
Effective-dated Policy
Idempotency
Soft Delete hanya untuk entity yang sesuai
```

Financial value menggunakan:

```text
DECIMAL
```

bukan:

```text
FLOAT
DOUBLE
```

---

# 2. Naming Convention

Table:

```text
snake_case
plural
```

Contoh:

```text
claims
claim_receipts
approval_policies
workflow_actions
```

Primary key:

```text
id
```

Foreign key:

```text
claim_id
user_id
role_id
```

Timestamp:

```text
created_at
updated_at
```

Optional deletion:

```text
deleted_at
```

---

# 3. Users

```text
users
-----------------------------------------
id BIGINT UNSIGNED PK
name VARCHAR(150)
email VARCHAR(255) UNIQUE
password VARCHAR(255)
is_active BOOLEAN
remember_token VARCHAR(100) NULL
created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
deleted_at TIMESTAMP NULL
```

Index:

```text
UNIQUE(email)
INDEX(is_active)
```

---

# 4. Roles

```text
roles
-----------------------------------------
id BIGINT UNSIGNED PK
name VARCHAR(100) UNIQUE
display_name VARCHAR(150)
description TEXT NULL
is_active BOOLEAN
created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Contoh role:

```text
ADMIN
EDP_OPERASIONAL
EDP_ADM
EDP_SUPERVISOR
EDP_MANAGER
FINANCE
DBM
BM
```

---

# 5. Role User

```text
role_user
-----------------------------------------
role_id BIGINT UNSIGNED FK
user_id BIGINT UNSIGNED FK
created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Primary/unique:

```text
UNIQUE(role_id, user_id)
```

Index:

```text
INDEX(user_id, role_id)
```

---

# 6. Permissions

```text
permissions
-----------------------------------------
id BIGINT UNSIGNED PK
name VARCHAR(150) UNIQUE
display_name VARCHAR(200)
description TEXT NULL
created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Contoh:

```text
claim.view
claim.create
claim.update
claim.submit
claim.review
claim.verify
claim.approve
claim.reject
claim.request_revision
claim.payment.process
claim.report.view
claim.report.export
audit.view
```

---

# 7. Permission Role

```text
permission_role
-----------------------------------------
permission_id BIGINT UNSIGNED FK
role_id BIGINT UNSIGNED FK
created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Constraint:

```text
UNIQUE(permission_id, role_id)
```

---

# 8. Claims

```text
claims
-----------------------------------------
id BIGINT UNSIGNED PK

claim_number VARCHAR(50) UNIQUE

requester_id BIGINT UNSIGNED FK

claim_type VARCHAR(30)

claim_date DATE

description TEXT NULL

total_amount DECIMAL(18,2)

current_state VARCHAR(50)

submitted_at DATETIME NULL
approved_at DATETIME NULL
paid_at DATETIME NULL

created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
deleted_at TIMESTAMP NULL
```

Recommended claim types:

```text
BBM
TOL
HEALTH
```

Recommended state:

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

# 9. Claims Index

```text
UNIQUE(claim_number)

INDEX(requester_id, claim_date)

INDEX(current_state, created_at)

INDEX(current_state, updated_at)

INDEX(claim_type, claim_date)
```

Untuk dashboard:

```text
INDEX(current_state, claim_type, created_at)
```

dapat ditambahkan setelah profiling query aktual.

---

# 10. Claim Receipts

```text
claim_receipts
-----------------------------------------
id BIGINT UNSIGNED PK

claim_id BIGINT UNSIGNED FK

receipt_number VARCHAR(100) NULL

transaction_date DATE

merchant VARCHAR(200)

amount DECIMAL(18,2)

description TEXT NULL

created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Constraint:

```text
amount > 0
```

ditegakkan melalui application/domain validation dan dapat diperkuat dengan MySQL CHECK.

Index:

```text
INDEX(claim_id)

INDEX(receipt_number)

INDEX(transaction_date)
```

---

# 11. Claim Documents

```text
claim_documents
-----------------------------------------
id BIGINT UNSIGNED PK

claim_id BIGINT UNSIGNED FK

claim_receipt_id BIGINT UNSIGNED NULL FK

document_type VARCHAR(50)

status VARCHAR(30)

original_filename VARCHAR(255)

storage_disk VARCHAR(100)

storage_path VARCHAR(500)

mime_type VARCHAR(100)

file_size BIGINT UNSIGNED

checksum_sha256 CHAR(64)

uploaded_by BIGINT UNSIGNED FK

received_at DATETIME NULL

verified_at DATETIME NULL

created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Index:

```text
INDEX(claim_id)

INDEX(claim_receipt_id)

INDEX(checksum_sha256)

INDEX(document_type, status)
```

---

# 12. Vehicles

```text
vehicles
-----------------------------------------
id BIGINT UNSIGNED PK

code VARCHAR(50) UNIQUE

name VARCHAR(100)

vehicle_type VARCHAR(30)

plate_number VARCHAR(20) NULL

is_active BOOLEAN

created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
deleted_at TIMESTAMP NULL
```

Vehicle type:

```text
MOTOR
CAR
```

---

# 13. BBM Standards

```text
bbm_standards
-----------------------------------------
id BIGINT UNSIGNED PK

vehicle_type VARCHAR(30)

standard_km_per_liter DECIMAL(10,2)

effective_from DATE

effective_until DATE NULL

is_active BOOLEAN

created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Default:

```text
MOTOR = 40.00
CAR = 13.00
```

Constraint:

```text
standard_km_per_liter > 0
```

Recommended unique/business rule:

```text
Tidak boleh ada dua active overlapping policy
untuk vehicle_type yang sama.
```

---

# 14. Claim BBM Details

```text
claim_bbm_details
-----------------------------------------
id BIGINT UNSIGNED PK

claim_id BIGINT UNSIGNED UNIQUE FK

vehicle_id BIGINT UNSIGNED FK

vehicle_type VARCHAR(30)

distance_km DECIMAL(12,2)

fuel_liter DECIMAL(12,2)

actual_km_per_liter DECIMAL(12,4)

standard_km_per_liter DECIMAL(10,2)

variance_km_per_liter DECIMAL(12,4)

standard_snapshot_source VARCHAR(100) NULL

created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Formula:

```text
actual_km_per_liter =
distance_km / fuel_liter
```

Server-side only.

---

# 15. Health Policies

```text
health_policies
-----------------------------------------
id BIGINT UNSIGNED PK

name VARCHAR(150)

annual_limit DECIMAL(18,2)

effective_from DATE

effective_until DATE NULL

is_active BOOLEAN

created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Default business policy:

```text
annual_limit = 10000000.00
```

---

# 16. Claim Health Details

```text
claim_health_details
-----------------------------------------
id BIGINT UNSIGNED PK

claim_id BIGINT UNSIGNED UNIQUE FK

health_policy_id BIGINT UNSIGNED FK

policy_limit_snapshot DECIMAL(18,2)

claim_year SMALLINT UNSIGNED

created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Snapshot wajib disimpan.

---

# 17. Workflow Definitions

```text
workflow_definitions
-----------------------------------------
id BIGINT UNSIGNED PK

code VARCHAR(100)

name VARCHAR(150)

version INT UNSIGNED

is_active BOOLEAN

created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Constraint:

```text
UNIQUE(code, version)
```

Contoh:

```text
REIMBURSEMENT
V1
```

---

# 18. Workflow Stages

```text
workflow_stages
-----------------------------------------
id BIGINT UNSIGNED PK

workflow_definition_id BIGINT UNSIGNED FK

code VARCHAR(100)

name VARCHAR(150)

sequence INT UNSIGNED

stage_type VARCHAR(50)

created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Contoh:

```text
EDP_REVIEW
SUPERVISOR_VERIFICATION
MANAGER_VERIFICATION
FINANCE_VERIFICATION
MANAGEMENT_APPROVAL
PAYMENT_PROCESS
```

Constraint:

```text
UNIQUE(workflow_definition_id, code)
```

---

# 19. Workflow Transitions

```text
workflow_transitions
-----------------------------------------
id BIGINT UNSIGNED PK

workflow_stage_id BIGINT UNSIGNED FK

action VARCHAR(50)

from_state VARCHAR(50)

to_state VARCHAR(50)

required_permission VARCHAR(150) NULL

created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Contoh:

```text
MANAGEMENT_APPROVAL
APPROVE
MANAGEMENT_APPROVAL
APPROVED
claim.approve
```

---

# 20. Workflow Instances

```text
workflow_instances
-----------------------------------------
id BIGINT UNSIGNED PK

claim_id BIGINT UNSIGNED UNIQUE FK

workflow_definition_id BIGINT UNSIGNED FK

workflow_version INT UNSIGNED

started_at DATETIME

completed_at DATETIME NULL

created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Constraint:

```text
UNIQUE(claim_id)
```

Satu claim hanya memiliki satu active workflow instance dalam model V1.

---

# 21. Workflow Actions

```text
workflow_actions
-----------------------------------------
id BIGINT UNSIGNED PK

claim_id BIGINT UNSIGNED FK

workflow_instance_id BIGINT UNSIGNED FK

transition_id BIGINT UNSIGNED NULL FK

from_state VARCHAR(50)

action VARCHAR(50)

to_state VARCHAR(50)

actor_id BIGINT UNSIGNED FK

actor_role VARCHAR(100)

reason TEXT NULL

notes TEXT NULL

approval_policy_id BIGINT UNSIGNED NULL FK

approval_policy_version INT UNSIGNED NULL

created_at TIMESTAMP NULL
```

Index:

```text
INDEX(claim_id, created_at)

INDEX(actor_id, created_at)

INDEX(action, created_at)

INDEX(approval_policy_id)
```

---

# 22. Approval Policies

```text
approval_policies
-----------------------------------------
id BIGINT UNSIGNED PK

workflow_stage_id BIGINT UNSIGNED FK

name VARCHAR(150)

version INT UNSIGNED

approval_rule VARCHAR(30)

minimum_approvals INT UNSIGNED

priority INT

min_amount DECIMAL(18,2) NULL

max_amount DECIMAL(18,2) NULL

claim_type VARCHAR(30) NULL

effective_from DATE

effective_until DATE NULL

is_active BOOLEAN

created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Example:

```text
name:
Management Approval

version:
1

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

# 23. Approval Policy Roles

```text
approval_policy_roles
-----------------------------------------
id BIGINT UNSIGNED PK

approval_policy_id BIGINT UNSIGNED FK

role_id BIGINT UNSIGNED FK

created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Constraint:

```text
UNIQUE(approval_policy_id, role_id)
```

Management policy:

```text
approval_policy = Management Approval

roles:
DBM
BM
```

---

# 24. Approval Policy Scopes

```text
approval_policy_scopes
-----------------------------------------
id BIGINT UNSIGNED PK

approval_policy_id BIGINT UNSIGNED FK

scope_type VARCHAR(50)

scope_value VARCHAR(150)

created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Contoh:

```text
scope_type:
DEPARTMENT

scope_value:
EDP
```

Untuk V1, tabel ini dapat digunakan hanya jika kebutuhan bisnis memang memerlukannya.

---

# 25. Approval Rule

Supported:

```text
ANY_OF
ALL_OF
N_OF
```

Current:

```text
ANY_OF
```

Current configuration:

```text
eligible roles:
DBM
BM

minimum approvals:
1
```

---

# 26. Management Approval Data

Representasi database:

```text
approval_policies
        │
        ▼
approval_policy_roles
        │
        ├── DBM
        └── BM
```

Tidak ada:

```text
approved_by_dbm
approved_by_bm
```

Decision:

```text
workflow_actions
```

Contoh:

```text
actor_role = BM
action = APPROVE
from_state = MANAGEMENT_APPROVAL
to_state = APPROVED
approval_policy_id = 3
approval_policy_version = 1
```

---

# 27. Claim Status Histories

```text
claim_status_histories
-----------------------------------------
id BIGINT UNSIGNED PK

claim_id BIGINT UNSIGNED FK

from_state VARCHAR(50) NULL

to_state VARCHAR(50)

action VARCHAR(50)

actor_id BIGINT UNSIGNED FK

reason TEXT NULL

notes TEXT NULL

created_at TIMESTAMP NULL
```

Status history:

```text
APPEND ONLY
```

Tidak boleh diedit oleh user biasa.

---

# 28. SLA Policies

```text
sla_policies
-----------------------------------------
id BIGINT UNSIGNED PK

workflow_stage_id BIGINT UNSIGNED NULL FK

name VARCHAR(150)

duration_minutes INT UNSIGNED

calendar_type VARCHAR(30)

effective_from DATE

effective_until DATE NULL

is_active BOOLEAN

created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Default recommendation:

```text
3 working days
```

Namun angka final dapat dikonfigurasi.

---

# 29. SLA Instances

```text
sla_instances
-----------------------------------------
id BIGINT UNSIGNED PK

claim_id BIGINT UNSIGNED FK

workflow_stage_id BIGINT UNSIGNED FK

sla_policy_id BIGINT UNSIGNED FK

started_at DATETIME

due_at DATETIME

completed_at DATETIME NULL

status VARCHAR(30)

duration_minutes INT UNSIGNED NULL

created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Index:

```text
INDEX(claim_id, status)

INDEX(status, due_at)
```

---

# 30. Payments

```text
payments
-----------------------------------------
id BIGINT UNSIGNED PK

claim_id BIGINT UNSIGNED UNIQUE FK

processed_by BIGINT UNSIGNED FK

amount DECIMAL(18,2)

payment_method VARCHAR(30)

bank_name VARCHAR(150)

bank_account_number VARCHAR(100)

bank_account_name VARCHAR(150)

payment_date DATETIME NULL

transfer_reference VARCHAR(150) NULL

payment_proof_document_id BIGINT UNSIGNED NULL FK

status VARCHAR(30)

idempotency_key VARCHAR(100) UNIQUE

notes TEXT NULL

created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Payment method V1:

```text
BANK_TRANSFER
```

Payment status:

```text
PENDING
PROCESSING
PAID
FAILED
```

---

# 31. Audit Events

```text
audit_events
-----------------------------------------
id BIGINT UNSIGNED PK

actor_id BIGINT UNSIGNED NULL FK

event_type VARCHAR(100)

entity_type VARCHAR(150)

entity_id BIGINT UNSIGNED

before_data JSON NULL

after_data JSON NULL

ip_address VARCHAR(45) NULL

user_agent TEXT NULL

created_at TIMESTAMP
```

Index:

```text
INDEX(actor_id, created_at)

INDEX(entity_type, entity_id, created_at)

INDEX(event_type, created_at)
```

Audit event bersifat append-only.

---

# 32. Notifications

```text
notifications
-----------------------------------------
id BIGINT UNSIGNED PK

user_id BIGINT UNSIGNED FK

type VARCHAR(100)

title VARCHAR(200)

message TEXT

reference_type VARCHAR(150) NULL

reference_id BIGINT UNSIGNED NULL

read_at DATETIME NULL

created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Index:

```text
INDEX(user_id, read_at, created_at)

INDEX(reference_type, reference_id)
```

---

# 33. Outbox Events

```text
outbox_events
-----------------------------------------
id BIGINT UNSIGNED PK

event_type VARCHAR(150)

aggregate_type VARCHAR(150)

aggregate_id BIGINT UNSIGNED

payload JSON

status VARCHAR(30)

attempts INT UNSIGNED DEFAULT 0

available_at DATETIME

processed_at DATETIME NULL

last_error TEXT NULL

created_at TIMESTAMP NULL
updated_at TIMESTAMP NULL
```

Index:

```text
INDEX(status, available_at)

INDEX(aggregate_type, aggregate_id)

INDEX(event_type, created_at)
```

---

# 34. Foreign Key Strategy

Core:

```text
claims.requester_id
    → users.id
```

Receipts:

```text
claim_receipts.claim_id
    → claims.id
```

Documents:

```text
claim_documents.claim_id
    → claims.id

claim_documents.claim_receipt_id
    → claim_receipts.id

claim_documents.uploaded_by
    → users.id
```

Workflow:

```text
workflow_instances.claim_id
    → claims.id

workflow_actions.claim_id
    → claims.id

workflow_actions.workflow_instance_id
    → workflow_instances.id

workflow_actions.actor_id
    → users.id
```

Approval:

```text
approval_policies.workflow_stage_id
    → workflow_stages.id

approval_policy_roles.approval_policy_id
    → approval_policies.id

approval_policy_roles.role_id
    → roles.id
```

Payment:

```text
payments.claim_id
    → claims.id

payments.processed_by
    → users.id
```

---

# 35. Financial Precision

Semua nominal:

```text
DECIMAL(18,2)
```

Contoh:

```text
10000000.00
```

Jangan menggunakan:

```text
FLOAT
DOUBLE
```

untuk:

```text
claim amount
receipt amount
payment amount
health limit
```

BBM:

```text
distance_km
fuel_liter
actual_km_per_liter
```

juga menggunakan `DECIMAL`.

---

# 36. Transaction Requirements

Transaction wajib digunakan pada:

```text
Submit Claim
Request Revision
Verify Claim
Approve Claim
Reject Claim
Process Payment
Complete Payment
```

Contoh approval:

```text
BEGIN

SELECT claims
WHERE id = ?
FOR UPDATE

validate state

resolve policy

validate role

validate permission

validate business rules

INSERT workflow_actions

UPDATE claims

INSERT claim_status_histories

INSERT audit_events

INSERT outbox_events

COMMIT
```

---

# 37. Concurrency

Approval harus menggunakan:

```text
SELECT ... FOR UPDATE
```

terhadap claim.

Skenario:

```text
DBM ────────┐
            │
            ▼
          CLAIM
            ▲
            │
BM ─────────┘
```

Jika DBM lebih dahulu melakukan approval:

```text
MANAGEMENT_APPROVAL
        ↓
APPROVED
```

BM yang kemudian approve:

```text
409 CONFLICT
```

Tidak boleh menghasilkan:

```text
duplicate APPROVED transition
```

---

# 38. Idempotency

Action kritis dapat menggunakan idempotency key pada API.

Terutama:

```text
payment
```

dan action lain yang berpotensi dikirim ulang oleh client.

Rule:

```text
same key
+
same payload
=
replay previous result
```

Sedangkan:

```text
same key
+
different payload
=
409 Conflict
```

---

# 39. Policy Versioning

Ketika approval dilakukan, workflow action menyimpan:

```text
approval_policy_id
approval_policy_version
```

Contoh:

```text
Policy:
Management Approval

Version:
3
```

Jika policy berubah:

```text
Version:
4
```

historical action tetap menunjukkan:

```text
Version 3
```

sehingga audit tidak bergantung pada konfigurasi policy saat ini.

---

# 40. Effective Date

Policy harus memenuhi:

```text
effective_from <= claim/action date
```

dan:

```text
effective_until IS NULL
OR
effective_until >= claim/action date
```

Policy yang expired tidak boleh dipakai untuk decision baru.

---

# 41. Policy Priority

Jika lebih dari satu policy match:

```text
priority DESC
```

Policy dengan priority lebih tinggi dipilih.

Namun database/application harus mencegah ambiguity seperti:

```text
same stage
same scope
same priority
overlapping effective period
```

---

# 42. Policy Resolution Example

Claim:

```text
claim_type = HEALTH
amount = 7,500,000
state = MANAGEMENT_APPROVAL
```

Actor:

```text
role = BM
permission = claim.approve
```

Resolver:

```text
1. Find stage
2. Find active policies
3. Filter effective date
4. Filter claim type
5. Filter amount
6. Resolve priority
7. Find eligible roles
8. Check BM
9. Return decision
```

Result:

```text
ALLOWED
```

---

# 43. Policy Denial Example

Actor:

```text
role = EDP_ADM
permission = claim.approve = false
```

Result:

```text
DENIED
```

Reason:

```text
ROLE_NOT_ELIGIBLE
```

atau:

```text
PERMISSION_DENIED
```

tergantung layer yang gagal.

---

# 44. Required CHECK Constraints

MySQL 8 dapat menggunakan CHECK constraints.

Contoh:

```text
claim_receipts.amount > 0
```

```text
claims.total_amount >= 0
```

```text
claim_bbm_details.distance_km > 0
```

```text
claim_bbm_details.fuel_liter > 0
```

```text
claim_bbm_details.standard_km_per_liter > 0
```

```text
payments.amount > 0
```

```text
approval_policies.minimum_approvals > 0
```

---

# 45. Referential Delete Rules

Untuk transactional records:

```text
ON DELETE RESTRICT
```

Untuk optional reference yang memang dapat null:

```text
ON DELETE SET NULL
```

Contoh:

```text
claim_documents.claim_receipt_id
```

dapat dipertimbangkan `SET NULL` bila receipt lifecycle memungkinkan.

Namun claim dan receipt utama tidak boleh dihapus sembarangan.

---

# 46. Soft Delete

Soft delete cocok untuk:

```text
users
vehicles
master/policy data
```

Tidak untuk:

```text
workflow_actions
claim_status_histories
audit_events
```

karena ketiganya merupakan historical evidence.

---

# 47. Audit Integrity

Tidak boleh:

```text
UPDATE audit_events
DELETE audit_events
```

melalui application biasa.

Audit harus merekam:

```text
who
what
when
entity
before
after
```

Untuk approval:

```text
actor
role
claim
policy
policy version
from state
to state
timestamp
```

---

# 48. Recommended Migration Order

Urutan migration:

```text
1. users
2. roles
3. permissions
4. role_user
5. permission_role

6. vehicles
7. bbm_standards
8. health_policies

9. workflow_definitions
10. workflow_stages
11. workflow_transitions

12. approval_policies
13. approval_policy_roles
14. approval_policy_scopes

15. claims
16. claim_receipts
17. claim_documents
18. claim_bbm_details
19. claim_health_details

20. workflow_instances
21. workflow_actions
22. claim_status_histories

23. sla_policies
24. sla_instances

25. payments

26. audit_events
27. notifications
28. outbox_events
```

Catatan: urutan migration harus tetap disesuaikan dengan dependency FK Laravel.

---

# 49. Seed Baseline Roles

Minimum:

```text
ADMIN
EDP_OPERASIONAL
EDP_ADM
EDP_SUPERVISOR
EDP_MANAGER
FINANCE
DBM
BM
```

---

# 50. Seed Baseline Management Policy

```text
workflow stage:
MANAGEMENT_APPROVAL

name:
Management Approval

version:
1

approval_rule:
ANY_OF

minimum_approvals:
1

eligible roles:
DBM
BM

is_active:
true
```

---

# 51. Baseline Workflow

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
MANAGEMENT_APPROVAL
  ↓
APPROVED
  ↓
PAYMENT_PROCESS
  ↓
PAID
```

Alternative:

```text
EDP_REVIEW
      ↓
NEED_REVISION

SUPERVISOR_VERIFICATION
      ↓
NEED_REVISION

MANAGER_VERIFICATION
      ↓
NEED_REVISION

FINANCE_VERIFICATION
      ↓
NEED_REVISION

MANAGEMENT_APPROVAL
      ↓
NEED_REVISION
```

Rejection:

```text
REVIEW / VERIFICATION / APPROVAL
              ↓
          REJECTED
```

---

# 52. Critical Business Invariants

### Claim

```text
claim.total_amount
=
SUM(claim_receipts.amount)
```

### BBM

```text
actual_km_per_liter
=
distance_km / fuel_liter
```

### Health

```text
paid_health_claims
must respect applicable annual policy
```

### Management Approval

```text
DBM OR BM
```

### Payment

```text
APPROVED → PAYMENT_PROCESS → PAID
```

### Payment uniqueness

```text
one claim = one payment record
```

### Workflow

```text
one valid transition at a time
```

### Audit

```text
every critical transition = history + audit
```

---

# 53. Important Anti-Patterns

Jangan membuat:

```text
claims.approved_by_dbm
claims.approved_by_bm
```

Jangan membuat:

```text
claims.approval_status
```

sebagai pengganti workflow state machine.

Jangan menyimpan role sebagai:

```text
users.role = "BM"
```

jika sistem mendukung N:M role.

Jangan menyimpan policy role sebagai:

```text
eligible_roles = "DBM,BM"
```

Gunakan relational table:

```text
approval_policy_roles
```

Jangan menggunakan:

```text
FLOAT
DOUBLE
```

untuk uang.

Jangan menghapus:

```text
workflow_actions
claim_status_histories
audit_events
```

---

# 54. Database Definition of Done

## Identity

* [ ] Users
* [ ] Roles
* [ ] Permissions
* [ ] Role-user
* [ ] Permission-role

## Claim

* [ ] Claims
* [ ] Claim receipts
* [ ] Claim documents
* [ ] BBM details
* [ ] Health details

## Workflow

* [ ] Workflow definitions
* [ ] Workflow stages
* [ ] Workflow transitions
* [ ] Workflow instances
* [ ] Workflow actions

## Approval

* [ ] Approval policies
* [ ] Policy roles
* [ ] Policy scopes
* [ ] Policy version
* [ ] Effective date
* [ ] Priority
* [ ] ANY_OF
* [ ] Minimum approvals

## Finance

* [ ] Payments
* [ ] Idempotency
* [ ] Payment proof

## Operations

* [ ] SLA
* [ ] Audit
* [ ] Notifications
* [ ] Outbox

## Security

* [ ] Foreign keys
* [ ] Unique constraints
* [ ] Indexes
* [ ] Check constraints
* [ ] Append-only history
* [ ] Transaction boundaries
* [ ] Concurrency protection

---

# 55. Final Database Architecture

```text
                         USERS
                           │
              ┌────────────┴────────────┐
              ▼                         ▼
            ROLES                   CLAIMS
              │                         │
       ┌──────┴──────┐          ┌───────┼────────┐
       ▼             ▼          ▼       ▼        ▼
 PERMISSIONS    APPROVAL     RECEIPTS DOCUMENT  DETAILS
                  POLICY
                    │
              ┌─────┴─────┐
              ▼           ▼
        POLICY ROLES    POLICY SCOPE
              │
          ┌───┴───┐
          ▼       ▼
         DBM     BM
          │       │
          └───┬───┘
              │
           ANY_OF
              │
        MINIMUM = 1
              │
              ▼
           APPROVED
              │
              ▼
           PAYMENT
              │
              ▼
             PAID
```

**Final architectural rule:**

> **Role menentukan kandidat approver. Permission menentukan kemampuan melakukan action. Approval Policy menentukan eligibility berdasarkan kondisi claim. Workflow menentukan validitas transition. Database menyimpan seluruh decision sebagai historical evidence.**

**End of DATABASE_SCHEMA.md**
