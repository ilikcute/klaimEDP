# USER ROLES

## Sistem Rekap, Tracking & Analisa Klaim / Reimbursement EDP

**Version:** 1.0
**Status:** Draft for Architecture & Development

---

# 1. Tujuan

Dokumen ini mendefinisikan:

* role;
* tanggung jawab;
* hak akses;
* batasan akses;
* workflow authority;
* separation of duties;
* authorization principle.

Authorization wajib diterapkan **server-side**.

Frontend hanya berfungsi sebagai UX layer dan bukan security boundary.

---

# 2. Role Matrix

| Role            | Input | Review | Verify | Approve | Payment | Admin |
| --------------- | ----: | -----: | -----: | ------: | ------: | ----: |
| EDP Operasional |     ✓ |      - |      - |       - |       - |     - |
| EDP Adm         |     ✓ |      ✓ |      - |       - |       - |     - |
| EDP Supervisor  |     - |      - |      ✓ |       - |       - |     - |
| EDP Manager     |     - |      - |      ✓ |       - |       - |     - |
| GA/Finance      |     - |      - |      ✓ |       - |       ✓ |     - |
| DBM             |     - |      - |      - |       ✓ |       - |     - |
| BM              |     - |      - |      - |       ✓ |       - |     - |
| Admin           |    ✓* |     ✓* |     ✓* |      ✓* |      ✓* |     ✓ |

`*` Admin dapat memiliki kemampuan administratif berdasarkan policy, tetapi seluruh action tetap harus tercatat dalam audit trail.

---

# 3. EDP Operasional

## Tujuan

Sebagai user operasional yang melakukan input data klaim.

## Permissions

```text
claim.create
claim.view.own
claim.update.own_draft
claim.submit.own
receipt.create
receipt.update.own
document.upload.own
document.view.own
```

## Tidak memiliki

```text
claim.verify
claim.approve
claim.pay
claim.manage
```

EDP Operasional tidak dapat memverifikasi atau menyetujui klaim.

---

# 4. EDP Adm

## Tujuan

Melakukan input dan administrative review awal.

## Permissions

```text
claim.create
claim.view
claim.update
claim.review
claim.request_revision
claim.reject
receipt.manage
document.manage
```

## Tanggung jawab

* memastikan data lengkap;
* memastikan dokumen tersedia;
* memeriksa kelengkapan nota;
* melakukan rekap awal;
* meneruskan klaim ke Supervisor.

EDP Adm bukan approval management.

---

# 5. EDP Supervisor

## Tujuan

Melakukan verification tingkat pertama.

## Permissions

```text
claim.view
claim.verify
claim.request_revision
claim.reject
receipt.view
document.view
history.view
```

## Fokus

* validitas transaksi;
* requester;
* receipt;
* nominal;
* BBM rule;
* health limit;
* evidence.

---

# 6. EDP Manager

## Tujuan

Melakukan verification tingkat manajerial.

## Permissions

```text
claim.view
claim.verify
claim.request_revision
claim.reject
history.view
analytics.view
```

## Fokus

* kewajaran klaim;
* compliance;
* histori;
* hasil verification;
* business policy.

Manager bukan payment executor.

---

# 7. GA/Finance

## Tujuan

Melakukan final verification dan pembayaran.

Role ini secara desain perlu dibedakan antara:

```text
FINANCE_VERIFIER
```

dan

```text
FINANCE_PAYER
```

meskipun implementasi awal dapat menggunakan satu role.

## Permissions

```text
claim.view
claim.final_verify
claim.request_revision
claim.reject
payment.create
payment.view
payment.process
document.view
document.upload
report.payment
```

## Separation of Duties

Idealnya user yang melakukan final verification tidak melakukan payment untuk claim yang sama.

Rule:

```text
Verifier != Payer
```

Jika organisasi mengharuskan satu role, sistem minimal harus mencatat kedua action secara eksplisit.

---

# 8. DBM

## Tujuan

Melakukan management approval.

## Permissions

```text
claim.view
claim.approve
claim.reject
claim.request_revision
history.view
```

DBM tidak memiliki hak:

```text
payment.process
```

kecuali policy organisasi secara eksplisit mengizinkan.

---

# 9. BM

## Tujuan

Melakukan final management approval.

## Permissions

```text
claim.view
claim.approve
claim.reject
claim.request_revision
history.view
```

BM tidak melakukan payment.

---

# 10. Admin

Admin merupakan role dengan privilege administratif tertinggi.

Namun:

> Admin tidak boleh menjadi bypass terhadap audit trail.

Admin dapat:

* manage users;
* manage roles;
* manage permissions;
* manage master data;
* configure reimbursement rules;
* configure BBM standards;
* configure Health limit;
* configure SLA;
* configure workflow policy;
* manage notification configuration;
* view reports;
* view audit trail.

Action administratif wajib tercatat.

---

# 11. Permission Model

Permission tidak boleh hanya berdasarkan role name.

Gunakan permission granular.

Contoh:

```text
claim.create
claim.view
claim.view_own
claim.view_all
claim.update
claim.submit
claim.review
claim.verify
claim.final_verify
claim.approve
claim.reject
claim.request_revision

receipt.create
receipt.update
receipt.delete
receipt.view

document.upload
document.view
document.download
document.delete

payment.view
payment.create
payment.process

report.view
report.export

audit.view

user.manage
role.manage
permission.manage

configuration.manage
workflow.manage
```

---

# 12. Ownership Rule

Untuk role input:

```text
EDP Operasional
```

default scope:

```text
OWN CLAIM
```

User tidak boleh mengubah claim milik user lain kecuali permission secara eksplisit mengizinkan.

---

# 13. Workflow Authority

| Stage                   | Authorized Role          |
| ----------------------- | ------------------------ |
| Create                  | EDP Operasional, EDP Adm |
| Submit                  | Claim Owner / EDP Adm    |
| EDP Review              | EDP Adm                  |
| Supervisor Verification | EDP Supervisor           |
| Manager Verification    | EDP Manager              |
| Finance Verification    | GA/Finance               |
| DBM Approval            | DBM                      |
| BM Approval             | BM                       |
| Payment                 | Finance                  |
| Configuration           | Admin                    |

---

# 14. Separation of Duties

Sistem harus mencegah konflik role yang berisiko.

Contoh:

```text
Requester
    ≠
Verifier
    ≠
Approver
    ≠
Payer
```

Minimal policy:

1. requester tidak boleh approve claim sendiri;
2. requester tidak boleh final verify claim sendiri;
3. verifier tidak idealnya menjadi payer;
4. payment hanya setelah approval;
5. setiap action dicatat.

---

# 15. Authorization Rules

Authorization harus memeriksa:

```text
Authenticated User
+
Role
+
Permission
+
Claim State
+
Claim Ownership
+
Workflow Stage
```

Contoh:

User memiliki permission `claim.approve`, tetapi claim sedang:

```text
SUBMITTED
```

Maka:

```text
403 Forbidden
```

karena claim belum berada pada approval stage.

---

# 16. Role vs Workflow State

Role tidak boleh otomatis berarti boleh melakukan semua action.

Contoh:

```text
BM
```

memiliki:

```text
claim.approve
```

tetapi hanya dapat melakukan approve apabila:

```text
current_state = PENDING_BM_APPROVAL
```

---

# 17. Admin Override

Jika Admin memiliki override:

```text
admin.workflow.override
```

maka:

1. alasan wajib;
2. action wajib tercatat;
3. previous state dicatat;
4. target state dicatat;
5. actor dicatat;
6. timestamp dicatat;
7. audit metadata disimpan.

Tidak boleh ada silent override.

---

# 18. Role Scope

Recommended scope:

| Role            | Data Scope         |
| --------------- | ------------------ |
| EDP Operasional | Own claims         |
| EDP Adm         | EDP claims         |
| Supervisor      | Assigned/EDP scope |
| Manager         | Management scope   |
| GA/Finance      | Finance scope      |
| DBM             | Approval scope     |
| BM              | Approval scope     |
| Admin           | Global             |

Scope dapat dikonfigurasi apabila organisasi berkembang.

---

# 19. Role Assignment

Role assignment harus:

* authenticated;
* authorized;
* auditable;
* tidak menghapus histori permission;
* mencatat actor yang mengubah role.

---

# 20. Audit Requirement

Perubahan:

* role;
* permission;
* workflow authority;
* configuration;
* user status;

harus menghasilkan audit event.

Contoh:

```text
ROLE_ASSIGNED
ROLE_REVOKED
PERMISSION_GRANTED
PERMISSION_REVOKED
USER_DISABLED
USER_ENABLED
CONFIG_UPDATED
```

---

# 21. Prinsip Utama

> **Role menentukan siapa yang boleh melakukan action; workflow menentukan kapan action tersebut boleh dilakukan.**

Keduanya harus divalidasi server-side.

---

**End of USER_ROLES.md**
