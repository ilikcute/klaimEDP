# API SPECIFICATION

## Sistem Rekap, Tracking & Analisa Klaim / Reimbursement EDP

**Version:** 1.0
**Base:** `/api`

---

# 1. API Principles

API harus:

* RESTful;
* authenticated;
* authorization-aware;
* server-side validated;
* idempotent untuk critical operations;
* transaction-safe;
* consistent error response;
* pagination-aware.

Authentication:

```text
Laravel Sanctum
```

---

# 2. Authentication

```http
POST /api/login
POST /api/logout
GET  /api/me
```

Login mengembalikan authenticated session/token sesuai implementasi Sanctum.

---

# 3. Claims

```http
GET    /api/claims
POST   /api/claims
GET    /api/claims/{claim}
PUT    /api/claims/{claim}
DELETE /api/claims/{claim}
```

Delete hanya diperbolehkan untuk draft sesuai policy.

---

# 4. Claim Creation

```http
POST /api/claims
```

Request:

```json
{
  "claim_type": "BBM",
  "claim_date": "2026-10-05",
  "description": "Kunjungan operasional"
}
```

Server:

* menentukan requester;
* membuat claim number;
* menghitung total;
* membuat workflow;
* state = `DRAFT`.

Requester tidak boleh dikirim sebagai trusted identity dari frontend.

---

# 5. Receipts

```http
GET    /api/claims/{claim}/receipts
POST   /api/claims/{claim}/receipts
PUT    /api/claims/{claim}/receipts/{receipt}
DELETE /api/claims/{claim}/receipts/{receipt}
```

Amount menggunakan:

```text
DECIMAL
```

Server menghitung:

```text
claim.total_amount
=
SUM(receipts.amount)
```

---

# 6. Documents

```http
POST   /api/claims/{claim}/documents
GET    /api/claims/{claim}/documents
GET    /api/documents/{document}/download
DELETE /api/documents/{document}
```

Download harus authorization-protected.

Tidak menggunakan public storage URL.

---

# 7. Submit

```http
POST /api/claims/{claim}/submit
```

Header:

```http
Idempotency-Key: UUID
```

Server melakukan:

1. authentication;
2. authorization;
3. lock claim;
4. validate state;
5. validate receipts;
6. validate documents;
7. validate business rules;
8. update state;
9. create workflow action;
10. create history;
11. create audit event;
12. commit.

---

# 8. EDP Review

```http
POST /api/claims/{claim}/review
```

Request:

```json
{
  "decision": "FORWARD",
  "notes": "Dokumen lengkap"
}
```

Allowed:

```text
FORWARD
REQUEST_REVISION
REJECT
```

---

# 9. Verification

Supervisor:

```http
POST /api/claims/{claim}/supervisor-verification
```

Manager:

```http
POST /api/claims/{claim}/manager-verification
```

Finance:

```http
POST /api/claims/{claim}/finance-verification
```

Request:

```json
{
  "decision": "VERIFY",
  "notes": "Data telah diverifikasi"
}
```

---

# 10. Revision

```http
POST /api/claims/{claim}/request-revision
```

Request:

```json
{
  "reason_code": "INVALID_DOCUMENT",
  "notes": "Mohon upload nota yang lebih jelas"
}
```

`reason_code` wajib.

---

# 11. Rejection

```http
POST /api/claims/{claim}/reject
```

Request:

```json
{
  "reason_code": "POLICY_VIOLATION",
  "notes": "Tidak memenuhi ketentuan reimbursement"
}
```

Reason wajib.

---

# 12. Management Approval

Karena policy:

```text
DBM OR BM
```

API tidak boleh memaksa dua approval.

Concept:

```http
POST /api/claims/{claim}/management-approval
```

Request:

```json
{
  "decision": "APPROVE",
  "notes": "Disetujui"
}
```

Backend menentukan apakah actor adalah:

```text
DBM
```

atau:

```text
BM
```

dan memvalidasi bahwa actor memang authorized management approver.

Setelah satu approval valid:

```text
MANAGEMENT_APPROVAL
        ↓
APPROVED
```

Approval kedua tidak diperlukan.

---

# 13. Payment

```http
POST /api/claims/{claim}/payment
GET  /api/claims/{claim}/payment
```

Request:

```json
{
  "amount": 450000,
  "payment_date": "2026-10-10",
  "bank_name": "BANK",
  "account_number": "123456",
  "account_name": "Nama",
  "reference_number": "TRX-001"
}
```

Header:

```http
Idempotency-Key: UUID
```

Server memastikan:

```text
claim.state == APPROVED
```

dan:

```text
payment.amount <= approved_amount
```

---

# 14. BBM

```http
GET /api/bbm/standards
POST /api/bbm/standards
GET /api/claims/{claim}/bbm
PUT /api/claims/{claim}/bbm
```

Calculation:

```text
actual = distance_km / fuel_liter
```

Standard diambil server-side berdasarkan effective date.

Frontend tidak boleh mengirim trusted `standard_km_per_liter` sebagai source of truth.

---

# 15. Health

```http
GET /api/health/summary
GET /api/health/utilization
GET /api/health/remaining
```

Response conceptual:

```json
{
  "year": 2026,
  "limit": 10000000,
  "paid": 3500000,
  "pending": 500000,
  "approved": 1000000,
  "remaining": 6500000
}
```

---

# 16. Dashboard

```http
GET /api/dashboard
```

Data role-aware:

```text
My Claims
Pending Review
Pending Verification
Pending Approval
Payment Queue
SLA At Risk
SLA Breached
```

---

# 17. Reports

```http
GET /api/reports/claims
GET /api/reports/payment
GET /api/reports/sla
GET /api/reports/bbm
GET /api/reports/health
GET /api/reports/historical
```

Export:

```http
GET /api/reports/claims/export
```

Future Excel export harus dilakukan asynchronously untuk dataset besar.

---

# 18. History

```http
GET /api/claims/{claim}/history
GET /api/claims/{claim}/timeline
```

Timeline harus berasal dari persisted history, bukan frontend-generated events.

---

# 19. Audit

```http
GET /api/audit-events
```

Hanya authorized role.

---

# 20. Standard Response

Success:

```json
{
  "success": true,
  "data": {},
  "message": "Success"
}
```

Validation:

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {}
}
```

---

# 21. Error Semantics

```text
401 = unauthenticated
403 = authenticated but forbidden
404 = resource not found
409 = state/concurrency/idempotency conflict
422 = validation/business validation
429 = rate limited
500 = unexpected server error
```

---

# 22. 409 Conflict

Contoh:

```text
Current state already changed by another actor.
```

atau:

```text
Idempotency key reused with different payload.
```

---

# 23. Authorization

Setiap endpoint workflow harus memvalidasi:

```text
User
+
Role
+
Permission
+
Ownership
+
Current State
+
Workflow Policy
```

Frontend tidak menjadi security boundary.

---

# 24. Idempotency

Critical endpoints:

```text
submit
verify
approve
payment
```

menggunakan:

```text
Idempotency-Key
```

Jika key sama dan payload sama:

```text
Replay previous result
```

Jika key sama tetapi payload berbeda:

```text
409 Conflict
```

---

# 25. Pagination

List endpoint menggunakan:

```text
?page=1
&per_page=25
```

Maximum:

```text
per_page <= 100
```

Default:

```text
25
```

---

# 26. Filtering

Claims:

```text
status
claim_type
requester
date_from
date_to
amount_min
amount_max
sla_status
```

Search:

```text
claim_number
receipt_number
merchant
requester
```

Semua search harus menggunakan indexed/query-optimized strategy.

---

# 27. API Security

* HTTPS pada production.
* Sanctum authentication.
* CSRF protection sesuai mode Sanctum.
* Mass-assignment protection.
* Request validation.
* Rate limiting.
* Private document authorization.
* Audit critical actions.
* Never trust frontend-calculated totals.

---

# 28. API Definition of Done

* [ ] Authentication.
* [ ] Authorization.
* [ ] Claim CRUD.
* [ ] Receipt management.
* [ ] Document upload/download.
* [ ] Submit.
* [ ] Review.
* [ ] Verification.
* [ ] Revision.
* [ ] Rejection.
* [ ] DBM/BM management approval.
* [ ] Payment.
* [ ] BBM calculation.
* [ ] Health utilization.
* [ ] Dashboard.
* [ ] Reports.
* [ ] History.
* [ ] Audit.
* [ ] Idempotency.
* [ ] Concurrency handling.
* [ ] Pagination.
* [ ] Consistent errors.

---

**End of API_SPEC.md**
