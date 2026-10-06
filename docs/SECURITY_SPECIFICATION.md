# SECURITY SPECIFICATION

## Sistem Rekap, Tracking & Analisa Klaim / Reimbursement EDP

**Version:** 1.0
**Security Model:** Server-side Authorization
**Authentication:** Laravel Sanctum

---

# 1. Security Objective

Sistem harus menjamin:

* confidentiality;
* integrity;
* authenticity;
* authorization;
* accountability;
* traceability.

---

# 2. Authentication

Gunakan Laravel Sanctum.

Semua endpoint private membutuhkan authentication.

Unauthenticated:

```text
401 Unauthorized
```

---

# 3. Authorization

Authorization menggunakan:

```text
Role
+
Permission
+
Ownership
+
Workflow State
+
Workflow Policy
```

Tidak boleh hanya:

```text
if role == ...
```

---

# 4. IDOR Protection

Jangan menganggap:

```text
/claims/123
```

berarti user boleh melihat claim 123.

Backend harus memeriksa ownership/scope.

Contoh:

```text
EDP Operasional
→ own claim

EDP Supervisor
→ authorized verification scope
```

---

# 5. Workflow Authorization

Contoh:

```text
BM
```

memiliki:

```text
claim.approve
```

tetapi claim:

```text
SUPERVISOR_VERIFICATION
```

maka action tetap:

```text
403 Forbidden
```

---

# 6. Mass Assignment

Jangan menerima field sensitif secara bebas.

Contoh field yang tidak boleh dipercaya dari frontend:

```text
requester_id
total_amount
current_state
approved_amount
approved_by
paid_at
```

Field tersebut ditentukan server.

---

# 7. Financial Integrity

Money:

```text
DECIMAL(18,2)
```

Backend menghitung total.

Invariant:

```text
Claim Total = SUM(Receipts)
```

---

# 8. Payment Security

Payment harus:

* approved;
* authorized;
* transactional;
* idempotent;
* auditable.

Invariant:

```text
payment.amount <= approved_amount
```

Dan:

```text
one successful payment / claim
```

---

# 9. Concurrency

Critical operations menggunakan database transaction + row locking.

Contoh:

```text
SELECT ... FOR UPDATE
```

Untuk:

* workflow transition;
* payment;
* annual health utilization check;
* configuration updates yang critical.

---

# 10. Idempotency

Critical API:

```text
SUBMIT
VERIFY
APPROVE
PAYMENT
```

menggunakan idempotency key.

Same key + same payload:

```text
replay
```

Same key + different payload:

```text
409 Conflict
```

---

# 11. File Security

Upload:

* validate MIME;
* validate extension;
* validate size;
* generate safe storage filename;
* private storage;
* authorization on download.

Jangan percaya filename dari client.

---

# 12. Document Access

Document download:

```text
authenticated
+
authorized
+
claim accessible
```

Baru file dapat diberikan.

Tidak menggunakan public predictable path.

---

# 13. File Metadata

Simpan:

```text
original_filename
mime_type
size
checksum
uploaded_by
uploaded_at
```

Checksum:

```text
SHA-256
```

berguna untuk integrity/duplicate detection.

---

# 14. SQL Injection

Gunakan:

* Eloquent;
* Query Builder;
* parameter binding.

Jangan concatenate user input ke SQL.

---

# 15. XSS

Output user-controlled text harus escaped.

Terutama:

* notes;
* description;
* merchant;
* rejection reason;
* revision notes.

---

# 16. CSRF

Sanctum SPA authentication harus menggunakan CSRF protection sesuai deployment architecture.

---

# 17. Rate Limiting

Rate limit minimal untuk:

```text
login
file upload
claim submit
workflow actions
payment
reports/export
```

---

# 18. Sensitive Data

Account number dan data reimbursement harus dianggap sensitive business data.

Logging tidak boleh membocorkan:

```text
password
authentication token
full sensitive document contents
```

---

# 19. Audit Trail

Audit event wajib untuk:

```text
LOGIN
LOGOUT
CREATE
UPDATE
SUBMIT
REVIEW
VERIFY
APPROVE
REJECT
REQUEST_REVISION
UPLOAD_DOCUMENT
DOWNLOAD_DOCUMENT
PAYMENT
ROLE_CHANGE
PERMISSION_CHANGE
CONFIG_CHANGE
```

---

# 20. Audit Immutability

Normal user:

```text
NO UPDATE
NO DELETE
```

terhadap audit records.

Admin juga tidak boleh melakukan silent modification.

---

# 21. Security Headers

Production sebaiknya menggunakan:

```text
Content-Security-Policy
X-Content-Type-Options
Referrer-Policy
Permissions-Policy
Strict-Transport-Security
```

sesuai deployment.

---

# 22. HTTPS

Production:

```text
HTTPS ONLY
```

Sensitive traffic tidak boleh melalui plain HTTP.

---

# 23. Secrets

Jangan menyimpan:

```text
APP_KEY
DB_PASSWORD
Telegram token
API keys
```

di source code.

Gunakan environment/secret management.

---

# 24. Telegram Security

Telegram token tidak boleh dikirim ke frontend.

Notification service berada di backend.

Failure Telegram tidak boleh mengubah claim state menjadi gagal.

---

# 25. Business Rule Security

Security bukan hanya authentication.

Harus dilindungi:

```text
Health annual limit
BBM standard
Claim total
Approval state
Payment amount
Workflow order
```

---

# 26. Approval Security

Management approval:

```text
DBM OR BM
```

harus memastikan:

```text
authorized_management_approver
+
correct_state
+
not_rejected
+
not_already_paid
```

Satu approval valid menyelesaikan management gate.

---

# 27. Privilege Escalation

User tidak boleh:

* mengubah role sendiri;
* memberikan permission sendiri;
* mengubah claim ownership;
* mengubah workflow state;
* mengubah payment status.

Semua harus melalui authorized administrative operation.

---

# 28. Data Retention

Retention policy harus ditentukan oleh perusahaan.

Setelah ditentukan, implementasikan:

* retention;
* archival;
* legal hold;
* backup;
* restoration testing.

---

# 29. Backup

Database backup harus:

* automated;
* encrypted;
* monitored;
* tested restore.

File storage juga harus memiliki backup strategy.

---

# 30. Security Testing

Minimal test:

```text
Unauthenticated → 401
Unauthorized → 403
Not found → 404
Invalid business state → 409
Validation → 422
```

Test juga:

* IDOR;
* mass assignment;
* duplicate payment;
* concurrent approval;
* concurrent payment;
* file authorization;
* role escalation;
* workflow bypass.

---

# 31. Security Definition of Done

* [ ] Sanctum.
* [ ] Server-side RBAC.
* [ ] Policy authorization.
* [ ] Ownership checks.
* [ ] IDOR protection.
* [ ] CSRF.
* [ ] Rate limiting.
* [ ] File security.
* [ ] Audit trail.
* [ ] Payment idempotency.
* [ ] Workflow concurrency.
* [ ] HTTPS.
* [ ] Secrets management.
* [ ] Security headers.
* [ ] Backup.
* [ ] Restore test.
* [ ] Security test suite.

---

**End of SECURITY_SPEC.md**
