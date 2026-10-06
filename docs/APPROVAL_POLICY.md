# APPROVAL_POLICY.md

# Sistem Rekap, Tracking & Analisa Klaim / Reimbursement EDP

**Document:** Approval Policy Specification
**Version:** 1.0
**Status:** Baseline Architecture
**Architecture:** Role + Policy Based Approval

---

# 1. Tujuan

Dokumen ini mendefinisikan mekanisme otorisasi approval pada Sistem Rekap, Tracking & Analisa Klaim / Reimbursement EDP.

Sistem menggunakan pendekatan:

```text
ROLE + POLICY
```

Role menentukan **kapabilitas dasar user**, sedangkan Policy menentukan **apakah user tersebut eligible untuk melakukan approval pada claim tertentu**.

Dengan pendekatan ini, sistem tidak meng-hardcode aturan approval langsung berdasarkan nama role di setiap controller atau frontend.

---

# 2. Prinsip Utama

Approval harus memenuhi dua lapisan:

```text
                 USER
                   │
                   ▼
             ROLE CHECK
                   │
          ┌────────┴────────┐
          │                 │
       Eligible          Not Eligible
          │                 │
          ▼                 ▼
      POLICY CHECK        DENY
          │
    ┌─────┴─────┐
    │           │
   PASS        FAIL
    │           │
    ▼           ▼
 APPROVE       DENY
```

Dengan demikian:

```text
Role ≠ Approval Permission
```

Memiliki role `DBM` atau `BM` saja belum otomatis berarti user dapat approve setiap claim.

User harus:

1. memiliki role yang eligible;
2. memiliki permission approval;
3. memenuhi approval policy;
4. claim berada pada state yang benar;
5. claim memenuhi seluruh business rules;
6. belum diselesaikan oleh approver lain;
7. tidak terkena concurrency conflict.

---

# 3. Management Approval

Tahap:

```text
MANAGEMENT_APPROVAL
```

menggunakan aturan:

```text
DBM OR BM
```

Artinya hanya **satu** approval management yang diperlukan.

Formal rule:

```text
ANY_OF(DBM, BM)
minimum_approvals = 1
```

Contoh:

```text
DBM APPROVE
     │
     ▼
 APPROVED
```

atau:

```text
BM APPROVE
    │
    ▼
 APPROVED
```

Tidak diperlukan:

```text
DBM APPROVE
     +
BM APPROVE
```

---

# 4. Separation of Responsibility

Sistem membedakan:

### Role

Menjawab:

> "User ini memiliki posisi/otoritas apa?"

Contoh:

```text
EDP_ADM
EDP_SUPERVISOR
EDP_MANAGER
FINANCE
DBM
BM
ADMIN
```

### Permission

Menjawab:

> "User ini boleh melakukan operasi apa?"

Contoh:

```text
claim.view
claim.create
claim.submit
claim.review
claim.verify
claim.approve
claim.payment.process
```

### Policy

Menjawab:

> "Apakah user ini boleh melakukan operasi tersebut pada claim tertentu?"

Contoh:

```text
Claim:
Rp 7.500.000
Type:
HEALTH

User:
BM

Role:
BM

Permission:
claim.approve

Policy:
eligible = true

Result:
APPROVED ACTION ALLOWED
```

---

# 5. Approval Decision

Approval decision harus mengikuti:

```text
Authorization
      +
Workflow State
      +
Approval Policy
      +
Business Rules
```

Secara formal:

```text
canApprove(user, claim)
=
hasRole(user, eligibleRole)
AND
hasPermission(user, "claim.approve")
AND
workflowAllows(claim, "APPROVE")
AND
policyAllows(user, claim)
AND
businessRulesSatisfied(claim)
```

Semua kondisi harus terpenuhi.

---

# 6. Policy Evaluation

Policy dievaluasi **di backend**.

Frontend tidak boleh menjadi sumber keputusan.

Flow:

```text
Vue
 │
 │ APPROVE
 ▼
Laravel API
 │
 ▼
Authentication
 │
 ▼
Authorization
 │
 ▼
Workflow Guard
 │
 ▼
Approval Policy Resolver
 │
 ▼
Business Rule Validation
 │
 ▼
DB Transaction
 │
 ▼
APPROVED
```

Frontend hanya menerima hasil:

```json
{
  "allowed_actions": [
    "APPROVE",
    "REQUEST_REVISION",
    "REJECT"
  ]
}
```

---

# 7. Policy Dimensions

Approval policy dapat menggunakan beberapa dimensi.

Minimal architecture harus mendukung:

```text
role
claim_type
amount
organizational_scope
effective_period
```

Optional:

```text
branch
department
region
requester
vehicle_type
risk_level
special_category
```

---

# 8. Claim Type

Policy dapat membedakan:

```text
BBM
TOL
HEALTH
```

Contoh:

```text
claim_type = HEALTH
eligible_roles = DBM,BM
```

Sedangkan policy lain dapat menentukan:

```text
claim_type = BBM
eligible_roles = DBM,BM
```

---

# 9. Amount Policy

Policy dapat menggunakan batas nominal.

Contoh:

```text
min_amount
max_amount
```

Contoh:

|         Min |         Max | Eligible |
| ----------: | ----------: | -------- |
|         Rp0 | Rp1.000.000 | DBM/BM   |
| Rp1.000.001 | Rp5.000.000 | DBM/BM   |
| Rp5.000.001 |           ∞ | DBM/BM   |

**Catatan:** contoh nominal di atas adalah contoh konfigurasi teknis, bukan keputusan bisnis final.

Nilai sebenarnya harus disimpan sebagai configuration/policy.

---

# 10. Role Eligibility

Policy menentukan role yang dapat memenuhi approval gate.

Contoh:

```json
{
  "eligible_roles": [
    "DBM",
    "BM"
  ]
}
```

Untuk management approval:

```text
eligible_roles = DBM,BM
minimum_approvals = 1
approval_rule = ANY_OF
```

---

# 11. Permission

Role eligibility tidak menggantikan permission.

Contoh:

```text
User A
Role:
DBM

Permission:
claim.approve = TRUE

Policy:
eligible = TRUE

=> ALLOWED
```

Sedangkan:

```text
User B
Role:
DBM

Permission:
claim.approve = FALSE

Policy:
eligible = TRUE

=> DENIED
```

Permission tetap menjadi security boundary.

---

# 12. Policy Priority

Jika terdapat beberapa policy yang aktif dan match terhadap claim, sistem harus mempunyai mekanisme priority.

Contoh:

```text
priority 100
    ↓
priority 50
    ↓
priority 10
```

Policy dengan priority tertinggi yang applicable menjadi policy utama.

Namun sistem harus mencegah dua policy aktif dengan:

```text
same scope
same priority
overlapping effective period
```

karena dapat menghasilkan keputusan ambigu.

---

# 13. Effective Dating

Policy harus mendukung periode berlaku.

Field:

```text
effective_from
effective_until
```

Contoh:

```text
Policy V1
2026-01-01 → 2026-06-30

Policy V2
2026-07-01 → NULL
```

Claim yang diproses harus dapat menentukan policy yang berlaku sesuai aturan versioning.

---

# 14. Policy Versioning

Policy tidak boleh menyebabkan historical claim berubah secara diam-diam.

Ketika claim memasuki workflow:

```text
workflow_version
approval_policy_version
```

harus dapat diketahui.

Contoh:

```text
Claim #EDP-2026-000123

Workflow:
REIMBURSEMENT_V1

Approval Policy:
MANAGEMENT_APPROVAL_V3
```

Jika policy berubah kemudian:

```text
MANAGEMENT_APPROVAL_V4
```

claim lama tetap dapat diaudit berdasarkan policy yang digunakan saat decision dibuat.

---

# 15. Approval Gate

Management approval diperlakukan sebagai **approval gate**.

```text
MANAGEMENT_APPROVAL
        │
        ▼
Evaluate Policy
        │
        ▼
Eligible Roles
        │
   ┌────┴────┐
   │         │
  DBM       BM
   │         │
   └────┬────┘
        ▼
 minimum_approvals = 1
        │
        ▼
     APPROVED
```

---

# 16. Approval Rule Types

Architecture harus mendukung setidaknya:

### ANY_OF

```text
DBM OR BM
```

Satu approver dari daftar sudah cukup.

```text
minimum_approvals = 1
```

### ALL_OF

Contoh future policy:

```text
DBM AND Finance
```

Semua role wajib approve.

### N_OF

Contoh future:

```text
3 dari 5 approver
```

Namun management approval saat ini menggunakan:

```text
ANY_OF
minimum_approvals = 1
eligible_roles = DBM,BM
```

---

# 17. Current Management Rule

Konfigurasi baseline:

```yaml
stage: MANAGEMENT_APPROVAL

approval_rule: ANY_OF

minimum_approvals: 1

eligible_roles:
  - DBM
  - BM
```

Interpretasi:

```text
DBM approve => gate satisfied

BM approve => gate satisfied

DBM + BM approve => gate juga satisfied,
tetapi BM tidak diwajibkan.
```

---

# 18. Approval Action

Approval action:

```text
APPROVE
```

harus menghasilkan workflow transition:

```text
MANAGEMENT_APPROVAL
        │
        │ APPROVE
        ▼
     APPROVED
```

Actor disimpan:

```text
actor_id
actor_role
```

Policy yang digunakan juga harus dapat dilacak.

Contoh audit:

```text
claim_id:
123

action:
APPROVE

actor_id:
87

actor_role:
BM

policy:
MANAGEMENT_APPROVAL_V3

result:
APPROVED

timestamp:
...
```

---

# 19. Revision

Approver dapat melakukan:

```text
REQUEST_REVISION
```

Jika policy mengizinkan.

Transition:

```text
MANAGEMENT_APPROVAL
        │
        │ REQUEST_REVISION
        ▼
   NEED_REVISION
```

Wajib ada:

```text
reason
notes
actor
timestamp
```

---

# 20. Rejection

Approver dapat melakukan:

```text
REJECT
```

jika policy/permission mengizinkan.

Transition:

```text
MANAGEMENT_APPROVAL
        │
        │ REJECT
        ▼
     REJECTED
```

Reject harus mencatat alasan.

---

# 21. Approval Locking

Approval harus concurrency-safe.

Contoh:

```text
DBM                    BM
 │                      │
 │ APPROVE              │ APPROVE
 │                      │
 ├──────────┐  ┌────────┤
            ▼  ▼
          DB LOCK
            │
            ▼
       Evaluate State
            │
       ┌────┴────┐
       │         │
   first       second
       │         │
 APPROVED      409
```

Actor kedua tidak boleh menghasilkan duplicate workflow transition.

---

# 22. Transaction Boundary

Approval harus berada dalam transaction:

```text
BEGIN TRANSACTION

SELECT claim
FOR UPDATE

Validate current state

Validate actor

Resolve policy

Validate approval rule

Record workflow action

Update claim state

Record status history

Record audit event

Create outbox event

COMMIT
```

Notification Telegram diproses setelah transaction melalui outbox/queue.

---

# 23. Stale State Protection

Backend harus selalu membaca state terbaru setelah lock.

Jangan:

```text
GET claim
↓
check state
↓
approve
```

tanpa lock.

Gunakan:

```text
BEGIN
↓
SELECT ... FOR UPDATE
↓
check state
↓
check policy
↓
approve
↓
COMMIT
```

---

# 24. 401 vs 403 vs 409

### 401 Unauthorized

User belum authenticated.

```text
401
```

### 403 Forbidden

User authenticated tetapi tidak memiliki role/permission/policy yang diperlukan.

```text
403
```

### 409 Conflict

User sebenarnya eligible, tetapi state claim sudah berubah karena actor lain.

```text
409
```

Contoh:

```text
BM membuka claim
DBM lebih dulu approve
BM menekan Approve

=> 409 Conflict
```

---

# 25. Policy Failure Reason

Backend sebaiknya menghasilkan machine-readable reason.

Contoh:

```json
{
  "allowed": false,
  "reason_code": "APPROVAL_POLICY_NOT_ELIGIBLE"
}
```

Kemungkinan reason:

```text
ROLE_NOT_ELIGIBLE
PERMISSION_DENIED
CLAIM_STATE_INVALID
POLICY_NOT_FOUND
POLICY_EXPIRED
AMOUNT_OUTSIDE_POLICY
CLAIM_TYPE_NOT_ALLOWED
ORGANIZATIONAL_SCOPE_DENIED
APPROVAL_ALREADY_SATISFIED
CONCURRENT_WORKFLOW_CHANGE
```

---

# 26. Allowed Actions

API detail claim harus mengembalikan:

```json
{
  "allowed_actions": [
    "APPROVE",
    "REQUEST_REVISION",
    "REJECT"
  ]
}
```

Frontend tidak menghitung sendiri.

Misalnya:

```text
User = DBM
Role = DBM
Permission = claim.approve
Policy = eligible
State = MANAGEMENT_APPROVAL

=> APPROVE
```

Tetapi:

```text
User = DBM
Role = DBM
Permission = claim.approve
Policy = not eligible

=> APPROVE tidak dikirim
```

---

# 27. Backend Policy Resolver

Laravel membutuhkan komponen khusus:

```text
ApprovalPolicyResolver
```

Contoh interface konseptual:

```php
resolve(
    Claim $claim,
    User $actor,
    WorkflowStage $stage
): ApprovalDecision
```

Result:

```text
ApprovalDecision
----------------
allowed
policy_id
policy_version
approval_rule
eligible_roles
minimum_approvals
reason_code
```

---

# 28. Approval Guard

Policy resolver tidak melakukan transition.

Tanggung jawab dipisahkan:

```text
ApprovalPolicyResolver
        │
        ▼
ApprovalDecision
        │
        ▼
ApprovalGuard
        │
        ▼
TransitionClaimAction
```

Dengan demikian:

```text
Policy
≠
Workflow
≠
Authorization
```

ketiganya tetap terpisah.

---

# 29. Recommended Laravel Components

```text
app/
├── Domain/
│   └── Approval/
│       ├── ApprovalPolicyResolver.php
│       ├── ApprovalDecision.php
│       ├── ApprovalRule.php
│       ├── ApprovalGuard.php
│       └── Exceptions/
│
├── Models/
│   ├── ApprovalPolicy.php
│   └── ApprovalDecisionLog.php
│
├── Policies/
│   └── ClaimPolicy.php
│
└── Actions/
    └── Workflow/
        └── ApproveClaimAction.php
```

---

# 30. Security Rule

Frontend tidak boleh melakukan:

```javascript
if (user.role === 'DBM') {
    showApproveButton()
}
```

sebagai sumber keputusan.

Frontend hanya menggunakan:

```javascript
claim.allowed_actions.includes('APPROVE')
```

Backend tetap melakukan seluruh validation.

---

# 31. Auditability

Setiap approval harus dapat menjawab:

```text
Siapa yang approve?
Role apa?
Claim apa?
Kapan?
State sebelum?
State sesudah?
Policy apa?
Policy version?
Approval rule?
Nominal claim?
Claim type?
IP/device jika diperlukan?
```

Contoh:

```text
Claim:
EDP-2026-000123

Actor:
Budi

Role:
BM

Action:
APPROVE

From:
MANAGEMENT_APPROVAL

To:
APPROVED

Policy:
MANAGEMENT_APPROVAL_V3

Rule:
ANY_OF

Required:
1

Timestamp:
2026-10-05 14:30:22
```

---

# 32. Anti-Pattern

Jangan membuat:

```text
approved_by_dbm
approved_at_dbm

approved_by_bm
approved_at_bm
```

karena akan membuat workflow sulit dikembangkan.

Jangan juga membuat:

```php
if ($user->role === 'DBM') {
    approve();
}
```

tanpa policy evaluation.

Jangan menentukan approval hanya dari frontend.

Jangan menganggap:

```text
Role DBM = automatically eligible
```

---

# 33. Final Approval Decision Formula

Formula resmi:

```text
APPROVAL_ALLOWED =
    AUTHENTICATED
    AND ROLE_ELIGIBLE
    AND PERMISSION_GRANTED
    AND CLAIM_STATE_ALLOWED
    AND POLICY_MATCHED
    AND BUSINESS_RULES_VALID
    AND APPROVAL_GATE_NOT_ALREADY_SATISFIED
```

Kemudian:

```text
MANAGEMENT_APPROVAL
+
ANY_OF(DBM, BM)
+
MINIMUM_APPROVALS = 1
```

menghasilkan:

```text
APPROVED
```

---

# 34. Current Business Rule

Untuk versi pertama sistem:

```text
EDP Supervisor
       ↓
EDP Manager
       ↓
Finance
       ↓
Management Approval
       ↓
     DBM OR BM
       ↓
   exactly 1 approval
       ↓
    APPROVED
       ↓
     Finance
       ↓
      PAID
```

Dengan catatan:

```text
DBM/BM adalah satu approval gate,
bukan dua approval stage.
```

---

# 35. Definition of Done

Approval Policy dianggap selesai apabila:

* [ ] Role dan permission terpisah.
* [ ] Policy terpisah dari role.
* [ ] Management approval menggunakan `ANY_OF`.
* [ ] Eligible roles = DBM, BM.
* [ ] Minimum approval = 1.
* [ ] DBM saja dapat menyelesaikan gate.
* [ ] BM saja dapat menyelesaikan gate.
* [ ] DBM + BM tidak diwajibkan.
* [ ] Policy dapat berdasarkan claim type.
* [ ] Policy dapat berdasarkan nominal.
* [ ] Policy mendukung effective date.
* [ ] Policy memiliki versioning.
* [ ] Policy dapat diaudit.
* [ ] Backend menjadi authoritative decision maker.
* [ ] Frontend menggunakan `allowed_actions`.
* [ ] Approval menggunakan database transaction.
* [ ] Claim menggunakan row locking.
* [ ] Concurrent approval menghasilkan satu winner.
* [ ] Actor kedua mendapatkan `409 Conflict`.
* [ ] Approval tidak dapat dilakukan pada state yang salah.
* [ ] Approval decision dapat ditelusuri melalui audit trail.

---

# 36. Final Architecture

```text
                       USER
                        │
                        ▼
                 AUTHENTICATION
                        │
                        ▼
                     ROLE
                        │
                        ▼
                   PERMISSION
                        │
                        ▼
                 WORKFLOW STATE
                        │
                        ▼
               APPROVAL POLICY
                        │
              ┌─────────┴─────────┐
              │                   │
             DBM                 BM
              │                   │
              └─────────┬─────────┘
                        │
                    ANY_OF
                        │
                  MINIMUM = 1
                        │
                        ▼
                     APPROVE
                        │
                        ▼
                    APPROVED
                        │
                        ▼
                  PAYMENT PROCESS
                        │
                        ▼
                      PAID
```

**Architecture principle:**

> **Role menentukan siapa yang berpotensi berwenang. Permission menentukan operasi yang boleh dilakukan. Policy menentukan apakah user tersebut eligible untuk claim tertentu. Workflow menentukan apakah action tersebut valid pada saat itu. Backend menjadi sumber kebenaran terakhir.**

---

**End of APPROVAL_POLICY.md**
