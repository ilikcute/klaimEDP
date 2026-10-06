# PRODUCT REQUIREMENT DOCUMENT

## Sistem Rekap, Tracking & Analisa Klaim / Reimbursement EDP

**Document:** PRD.md
**Version:** 1.0
**Status:** Baseline Draft
**Business Domain:** Retail
**Department:** EDP
**Backend:** PHP / Laravel
**Frontend:** Vue.js
**Database:** MySQL
**Authentication:** Laravel Sanctum
**Integration:** Telegram Bot
**Platform:** Web Application
**Design:** Mobile First / Online

---

# 1. Document Purpose

Dokumen Product Requirement Document (PRD) ini mendefinisikan kebutuhan fungsional dan non-fungsional sistem Rekap, Tracking & Analisa Klaim/Reimbursement EDP.

PRD merupakan turunan dari Business Requirement Document (BRD).

PRD digunakan sebagai acuan untuk:

* desain sistem;
* desain database;
* desain workflow;
* desain API;
* desain frontend;
* desain UI/UX;
* development Laravel;
* development Vue.js;
* testing;
* UAT;
* audit;
* acceptance criteria.

---

# 2. Product Vision

Membangun platform terpusat yang mampu mengelola seluruh siklus reimbursement EDP mulai dari:

```text
Create
   ↓
Input Receipt
   ↓
Upload Document
   ↓
Submit
   ↓
Review
   ↓
Verification
   ↓
Approval
   ↓
Payment
   ↓
Historical Analysis
```

Sistem tidak hanya berfungsi sebagai aplikasi pencatatan, tetapi juga sebagai:

> **Workflow Management + Document Management + Audit Trail + Historical Analytics Platform.**

---

# 3. Product Goals

## 3.1 Primary Goals

Sistem harus:

1. Memudahkan pembuatan reimbursement.
2. Memudahkan upload nota.
3. Mendukung satu claim dengan banyak nota.
4. Menjamin seluruh nota dalam satu claim berasal dari user yang sama.
5. Mengelola workflow reimbursement.
6. Mengelola verifikasi bertingkat.
7. Mengelola approval bertingkat.
8. Mengelola pembayaran.
9. Menyimpan histori.
10. Menyediakan audit trail.
11. Menghitung SLA.
12. Mendeteksi SLA breach.
13. Menghasilkan historical average.
14. Mengelola standard BBM.
15. Menghitung actual BBM ratio.
16. Membandingkan actual dengan standard.
17. Mengelola annual health limit.
18. Menyediakan dashboard.
19. Menyediakan reporting.
20. Mendukung notifikasi Telegram.

---

# 4. Target Users

## 4.1 EDP Operasional

Pengguna yang melakukan input reimbursement.

## 4.2 EDP Adm

Pengguna yang melakukan input/rekap awal dan proses administratif awal.

## 4.3 EDP Supervisor

Pengguna yang melakukan verifikasi awal.

## 4.4 EDP Manager

Pengguna yang melakukan verifikasi lanjutan.

## 4.5 GA / Finance

Pengguna yang melakukan verifikasi final dan payment.

## 4.6 DBM

Approver level manajemen.

## 4.7 BM

Approver level manajemen.

## 4.8 System Administrator

Mengelola:

* user;
* role;
* permission;
* master data;
* configuration;
* system settings.

---

# 5. Core Domain

Entitas utama sistem adalah:

```text
Claim
```

Claim memiliki:

```text
Claim
├── Claim Header
├── Claim Items / Receipts
├── Documents
├── Verification
├── Approval
├── Payment
├── Status History
└── Audit Trail
```

---

# 6. Claim Lifecycle

Lifecycle utama:

```text
DRAFT
  ↓
SUBMITTED
  ↓
EDP ADM REVIEW
  ↓
SUPERVISOR VERIFICATION
  ↓
MANAGER VERIFICATION
  ↓
FINANCE VERIFICATION
  ↓
DBM APPROVAL
  ↓
BM APPROVAL
  ↓
APPROVED
  ↓
PAYMENT PROCESS
  ↓
PAID
```

Alternative transition:

```text
NEED_REVISION
REJECTED
CANCELLED
```

---

# 7. Functional Requirement Catalog

## Claim Management

| ID           | Requirement     |
| ------------ | --------------- |
| FR-CLAIM-001 | Create Claim    |
| FR-CLAIM-002 | Multi Receipt   |
| FR-CLAIM-003 | Upload Document |
| FR-CLAIM-004 | Submit Claim    |

## Workflow

| ID        | Requirement             |
| --------- | ----------------------- |
| FR-WF-001 | EDP Adm Review          |
| FR-WF-002 | Supervisor Verification |
| FR-WF-003 | Manager Verification    |
| FR-WF-004 | Finance Verification    |
| FR-WF-005 | DBM Approval            |
| FR-WF-006 | BM Approval             |
| FR-WF-007 | Payment                 |

## SLA

| ID         | Requirement        |
| ---------- | ------------------ |
| FR-SLA-001 | SLA Calculation    |
| FR-SLA-002 | SLA Breach         |
| FR-SLA-003 | Historical Average |

## BBM

| ID         | Requirement         |
| ---------- | ------------------- |
| FR-BBM-001 | Vehicle Standard    |
| FR-BBM-002 | Actual KM/L         |
| FR-BBM-003 | Standard Comparison |

## Health

| ID            | Requirement     |
| ------------- | --------------- |
| FR-HEALTH-001 | Annual Limit    |
| FR-HEALTH-002 | Utilization     |
| FR-HEALTH-003 | Remaining Limit |

## Audit

| ID           | Requirement   |
| ------------ | ------------- |
| FR-AUDIT-001 | Claim History |
| FR-AUDIT-002 | Audit Trail   |

---

# 8. FR-CLAIM-001 — Create Claim

## Objective

Memungkinkan user yang mempunyai permission untuk membuat reimbursement claim.

## Actor

* EDP Operasional
* EDP Adm
* Admin

## Preconditions

User:

* authenticated;
* active;
* mempunyai permission `claim.create`.

## Functional Requirements

System MUST allow authorized user to create a claim.

Claim minimal memiliki:

* claim number;
* requester;
* claim type;
* claim date/period;
* description;
* total amount;
* status;
* created by;
* created timestamp.

Claim type:

```text
BBM
TOL
HEALTH
```

## Default Status

Claim baru:

```text
DRAFT
```

## Rules

1. Requester harus merupakan user/personil EDP yang valid.
2. Claim type wajib dipilih.
3. Claim yang masih DRAFT dapat diedit sesuai permission.
4. Claim yang sudah masuk workflow tidak boleh diedit bebas.
5. Perubahan data setelah workflow berjalan harus mengikuti mekanisme revision.
6. Claim number harus unik.
7. Created timestamp harus berasal dari server.
8. User tidak boleh memanipulasi ownership claim melalui request frontend.

## Acceptance Criteria

### AC-001

Given user mempunyai permission create claim,

When user membuat claim valid,

Then system membuat claim dengan status `DRAFT`.

### AC-002

Given claim sudah dibuat,

When claim number dihasilkan,

Then claim number harus unique.

### AC-003

Given user mencoba membuat claim dengan requester lain tanpa permission,

Then system menolak request.

### AC-004

Given claim DRAFT,

When user melakukan edit,

Then system mengizinkan perubahan sesuai permission.

---

# 9. FR-CLAIM-002 — Multi Receipt

## Objective

Satu claim dapat mempunyai banyak nota.

## Rules

1. Satu claim dapat mempunyai satu atau lebih receipt.
2. Semua receipt dalam claim harus dimiliki oleh requester yang sama.
3. Setiap receipt memiliki nominal sendiri.
4. Total claim dihitung dari receipt/item.
5. User tidak boleh memasukkan receipt milik personil lain.
6. Receipt harus dapat diidentifikasi secara individual.

## Receipt Data

Minimal:

```text
receipt_date
receipt_number
merchant
description
amount
```

Untuk BBM dapat ditambahkan:

```text
vehicle_type
vehicle_identifier
distance_km
fuel_liter
```

Untuk kesehatan dapat ditambahkan:

```text
medical_provider
patient
medical_category
```

## Total

Secara konsep:

```text
Claim Total = SUM(Receipt Amount)
```

Total harus dihitung/validated server-side.

Frontend tidak boleh menjadi sumber kebenaran nominal.

## Acceptance Criteria

### AC-001

User dapat menambahkan lebih dari satu receipt.

### AC-002

System menghitung total dari seluruh receipt.

### AC-003

Receipt dari user berbeda tidak dapat dimasukkan ke claim yang sama.

### AC-004

Menghapus receipt dari claim harus tercatat apabila claim sudah pernah masuk workflow.

---

# 10. FR-CLAIM-003 — Upload Document

## Objective

Menyediakan dokumentasi digital reimbursement.

## Supported Document

Minimal:

* Foto nota;
* Scan;
* PDF;
* Bukti transfer;
* Dokumen pendukung.

## Requirements

System harus melakukan validasi:

* MIME type;
* extension;
* file size;
* filename;
* upload authorization.

## Security

File reimbursement harus disimpan pada protected/private storage.

Akses file harus melalui authorization.

Tidak diperbolehkan memberikan akses public hanya berdasarkan URL file.

## Document Metadata

Minimal:

```text
document_id
claim_id
receipt_id
document_type
original_filename
storage_path
mime_type
file_size
checksum
uploaded_by
uploaded_at
```

## Versioning

Dokumen yang diganti karena revision harus tetap dapat ditelusuri melalui histori.

## Acceptance Criteria

### AC-001

User dapat upload foto nota yang valid.

### AC-002

System menolak file dengan MIME type yang tidak diizinkan.

### AC-003

User tanpa permission tidak dapat mengakses dokumen claim lain.

### AC-004

Dokumen memiliki metadata uploader dan timestamp.

---

# 11. FR-CLAIM-004 — Submit Claim

## Objective

Mengubah claim dari DRAFT menjadi SUBMITTED.

## Preconditions

Sebelum submit:

* claim valid;
* claim type tersedia;
* minimal satu receipt;
* total amount valid;
* dokumen wajib tersedia sesuai jenis claim;
* requester valid.

## Transition

```text
DRAFT
 ↓
SUBMITTED
```

## Rules

1. Submit harus dilakukan oleh requester atau authorized user.
2. Setelah submit, claim tidak boleh diedit secara bebas.
3. Submit harus membuat history.
4. Submit harus membuat audit log.
5. Submit dapat memicu Telegram notification.

## Acceptance Criteria

### AC-001

Claim valid dapat disubmit.

### AC-002

Claim tanpa receipt tidak dapat disubmit.

### AC-003

Claim invalid tidak dapat disubmit.

### AC-004

Submit menghasilkan history.

### AC-005

Submit menghasilkan audit trail.

---

# 12. FR-WF-001 — EDP Adm Review

## Objective

Melakukan review administratif awal.

## Actor

EDP Adm.

## Responsibilities

* memeriksa kelengkapan;
* memeriksa data;
* memeriksa receipt;
* memastikan dokumen;
* melakukan rekap;
* meneruskan claim;
* meminta revision;
* reject jika sesuai kewenangan.

## Transition

```text
SUBMITTED
    ↓
EDP ADM REVIEW
```

Jika valid:

```text
EDP ADM
   ↓
SUPERVISOR
```

Jika perlu perbaikan:

```text
EDP ADM
   ↓
NEED_REVISION
```

## Acceptance Criteria

System harus menyimpan:

* reviewer;
* timestamp;
* decision;
* notes;
* reason jika revision/rejection.

---

# 13. FR-WF-002 — Supervisor Verification

## Actor

EDP Supervisor.

## Objective

Melakukan verifikasi awal terhadap claim.

## Verification

Supervisor dapat memeriksa:

* claimant;
* receipt;
* nominal;
* dokumen;
* jenis reimbursement;
* aturan reimbursement;
* BBM ratio;
* health limit.

## Decision

```text
VERIFY
REQUEST REVISION
REJECT
```

## Acceptance Criteria

Setiap keputusan menghasilkan history dan audit trail.

---

# 14. FR-WF-003 — Manager Verification

## Actor

EDP Manager.

## Objective

Melakukan verifikasi lanjutan.

Manager dapat:

* verify;
* request revision;
* reject;
* meneruskan ke Finance/GA.

## Rule

Manager tidak boleh melakukan verification terhadap claim tanpa melalui tahapan workflow yang dipersyaratkan, kecuali user mempunyai permission khusus yang memang mengizinkan bypass.

---

# 15. FR-WF-004 — Finance Verification

## Actor

GA / Finance.

## Objective

Final verification sebelum management approval.

Finance memeriksa:

* nominal;
* dokumen;
* kelengkapan;
* hasil verifikasi sebelumnya;
* aturan reimbursement;
* payment readiness.

Decision:

```text
VERIFY
REQUEST REVISION
REJECT
```

Setelah final verification:

```text
FINANCE VERIFIED
      ↓
APPROVAL
```

---

# 16. FR-WF-005 — DBM Approval

## Actor

DBM.

## Objective

Management approval.

Default workflow:

```text
Finance Verified
       ↓
DBM
       ↓
BM
```

DBM dapat:

* approve;
* reject;
* request revision jika policy mengizinkan.

Approval wajib menyimpan:

* approver;
* timestamp;
* decision;
* notes.

---

# 17. FR-WF-006 — BM Approval

## Actor

BM.

## Objective

Final management approval.

Default:

```text
DBM APPROVED
     ↓
BM APPROVAL
```

Setelah BM approved:

```text
APPROVED
   ↓
PAYMENT
```

System harus mendukung konfigurasi approval:

```text
DBM + BM
```

atau:

```text
DBM OR BM
```

berdasarkan approval policy.

---

# 18. FR-WF-007 — Payment

## Actor

Finance.

## Preconditions

* seluruh approval wajib selesai;
* claim tidak rejected;
* claim belum paid.

## Payment Method

```text
BANK TRANSFER
```

## Payment Data

Minimal:

```text
payment_date
payment_amount
bank_name
account_number
account_name
transfer_reference
payment_proof
processed_by
```

## Transition

```text
APPROVED
    ↓
PAYMENT_PROCESS
    ↓
PAID
```

## Rules

1. Payment amount tidak boleh melebihi approved amount.
2. Claim PAID tidak boleh dibayar ulang.
3. Payment wajib memiliki timestamp.
4. Payment wajib masuk audit trail.
5. Bukti transfer dapat di-upload.

---

# 19. FR-SLA-001 — SLA Calculation

## Objective

Mengukur waktu proses claim.

## Default SLA

```text
3 WORKING DAYS
```

SLA harus configurable.

## Measurement

Minimal mengukur:

```text
Submission
     ↓
EDP Adm Review
     ↓
Supervisor Verification
     ↓
Manager Verification
     ↓
Finance Verification
     ↓
DBM Approval
     ↓
BM Approval
     ↓
Payment
```

System harus dapat menghitung:

* total processing duration;
* duration per stage;
* duration per actor/role;
* waiting duration;
* active processing duration.

## Working Day

Perhitungan SLA harus configurable agar dapat memperhitungkan:

* hari kerja;
* weekend;
* hari libur;
* jam kerja.

---

# 20. FR-SLA-002 — SLA Breach

## Objective

Mendeteksi claim yang melebihi SLA.

Status:

```text
ON_TIME
AT_RISK
BREACHED
```

## At Risk

At Risk harus configurable.

Contoh default:

```text
Remaining SLA <= 20%
```

## Breached

Jika:

```text
Actual Duration > SLA Target
```

maka claim:

```text
BREACHED
```

## Notification

SLA breach dapat memicu Telegram notification kepada role yang relevan.

---

# 21. FR-SLA-003 — Historical Average

## Objective

Menggunakan data histori untuk mengetahui rata-rata waktu proses.

System harus dapat menghitung:

```text
Average Processing Time
Average Verification Time
Average Approval Time
Average Payment Time
```

Berdasarkan:

* periode;
* claim type;
* role;
* user;
* workflow stage.

## Example

```text
Historical Average:
2.4 working days

Current SLA:
3 working days
```

Data historical dapat digunakan management untuk menentukan SLA yang lebih realistis.

---

# 22. FR-BBM-001 — Vehicle Standard

## Objective

Mengelola standard konsumsi BBM.

## Default Standard

| Vehicle Type | Standard |
| ------------ | -------: |
| Motor        |  40 KM/L |
| Mobil        |  13 KM/L |

Standard harus configurable.

## Future

Sistem dapat mendukung:

* vehicle model;
* vehicle category;
* effective date;
* different standard;
* historical standard.

Perubahan standard tidak boleh mengubah hasil histori lama.

Artinya standard yang digunakan ketika claim diproses harus dapat ditelusuri.

---

# 23. FR-BBM-002 — Actual KM/L

## Objective

Menghitung konsumsi BBM aktual.

Input:

```text
distance_km
fuel_liter
```

Calculation:

```text
Actual KM/L = Distance KM / Fuel Liter
```

Contoh:

```text
400 KM
10 Liter

Actual:
40 KM/L
```

System harus melakukan validasi:

* distance > 0;
* fuel > 0;
* numeric precision valid.

---

# 24. FR-BBM-003 — Standard Comparison

## Objective

Membandingkan actual consumption dengan standard.

Example:

```text
Standard:
40 KM/L

Actual:
35 KM/L

Variance:
-5 KM/L
```

System harus dapat menentukan:

```text
COMPLIANT
BELOW_STANDARD
ABOVE_STANDARD
```

Business rule final mengenai toleransi variance harus configurable.

System harus menyimpan standard yang digunakan saat transaksi diproses.

---

# 25. FR-HEALTH-001 — Annual Limit

## Objective

Mengelola batas reimbursement kesehatan.

Default:

```text
Rp10.000.000 / user / year
```

Limit harus configurable.

System harus dapat mendukung perubahan limit untuk periode tertentu.

---

# 26. FR-HEALTH-002 — Utilization

## Objective

Menghitung penggunaan limit kesehatan.

Contoh:

```text
Annual Limit:
Rp10.000.000

Approved/Paid:
Rp6.000.000

Utilization:
60%
```

Basis perhitungan final:

```text
SUBMITTED
APPROVED
atau
PAID
```

harus menjadi configuration/business rule.

Default yang direkomendasikan:

> **PAID** sebagai utilization final.

Namun amount yang masih pending dapat ditampilkan sebagai:

```text
Pending Utilization
```

agar user mengetahui potensi penggunaan limit.

---

# 27. FR-HEALTH-003 — Remaining Limit

## Objective

Menampilkan sisa annual health limit.

Calculation:

```text
Remaining =
Annual Limit - Utilized Amount
```

Contoh:

```text
Limit:
Rp10.000.000

Used:
Rp6.500.000

Remaining:
Rp3.500.000
```

System harus mencegah reimbursement yang melampaui limit sesuai policy.

---

# 28. FR-AUDIT-001 — Claim History

## Objective

Menyediakan histori lengkap perjalanan claim.

History minimal:

```text
Created
Updated
Submitted
Reviewed
Verified
Revision Requested
Resubmitted
Approved
Rejected
Payment Processed
Paid
```

Setiap history menyimpan:

```text
claim_id
from_status
to_status
actor
timestamp
reason
notes
metadata
```

History tidak boleh dihapus oleh user biasa.

---

# 29. FR-AUDIT-002 — Audit Trail

## Objective

Mencatat aktivitas penting user dan sistem.

Contoh action:

```text
LOGIN
LOGOUT
CREATE
UPDATE
DELETE
SUBMIT
REVIEW
VERIFY
APPROVE
REJECT
REQUEST_REVISION
UPLOAD_DOCUMENT
DOWNLOAD_DOCUMENT
PAYMENT
```

Audit trail minimal menyimpan:

```text
actor
action
entity
entity_id
timestamp
IP address
user agent
metadata
```

Untuk aktivitas sensitif, metadata harus cukup untuk melakukan forensic investigation.

---

# 30. Dashboard Requirements

Dashboard harus bersifat role-aware.

## EDP Operasional

Menampilkan:

* My Draft;
* Submitted;
* Need Revision;
* Approved;
* Paid.

## EDP Adm

Menampilkan:

* Pending Review;
* Revision;
* Claim by period;
* Claim by type.

## Supervisor / Manager

Menampilkan:

* Pending Verification;
* SLA At Risk;
* SLA Breached;
* Revision;
* Rejected.

## Finance

Menampilkan:

* Pending Final Verification;
* Approved;
* Pending Payment;
* Paid.

## Management

Menampilkan:

* total claim;
* total amount;
* monthly trend;
* yearly trend;
* claim by type;
* claim by employee;
* SLA compliance;
* average processing time;
* revision rate;
* rejection rate;
* BBM compliance;
* health utilization.

---

# 31. Reporting Requirements

Minimum report:

## Report 1 — Claim List

Filter:

* date;
* employee;
* claim type;
* status.

## Report 2 — Historical Claim

Filter:

* year;
* month;
* employee;
* type.

## Report 3 — Payment

Filter:

* payment date;
* employee;
* bank;
* status.

## Report 4 — SLA

Menampilkan:

* target;
* actual;
* variance;
* status.

## Report 5 — BBM

Menampilkan:

* vehicle;
* distance;
* liter;
* actual KM/L;
* standard KM/L;
* variance.

## Report 6 — Health

Menampilkan:

* annual limit;
* utilized;
* pending;
* remaining;
* utilization percentage.

---

# 32. Notification Requirements

Telegram integration harus mendukung event-based notification.

Events:

```text
CLAIM_SUBMITTED
CLAIM_NEED_REVISION
CLAIM_VERIFIED
CLAIM_APPROVED
CLAIM_REJECTED
PAYMENT_COMPLETED
SLA_AT_RISK
SLA_BREACHED
```

Notification tidak boleh membocorkan data sensitif kepada pihak yang tidak berhak.

---

# 33. Authorization Requirements

Frontend permission check hanya berfungsi sebagai UX.

Security enforcement wajib dilakukan di backend.

Contoh:

```text
EDP Operasional
```

tidak boleh melakukan:

```text
approve
verify
payment
```

hanya karena UI button disembunyikan.

Laravel harus tetap melakukan:

```text
Authentication
+
Authorization
+
Policy
+
Permission
```

pada setiap protected action.

---

# 34. Non-Functional Requirements

## NFR-001 — Security

System harus menerapkan:

* Laravel Sanctum;
* RBAC;
* authorization policy;
* validation;
* CSRF protection sesuai architecture;
* secure file handling;
* audit trail;
* rate limiting untuk endpoint sensitif.

## NFR-002 — Performance

Dengan baseline:

```text
±50 users
±500 claims/month
```

system harus mampu memberikan pengalaman penggunaan yang responsif.

## NFR-003 — Scalability

System harus mampu berkembang menjadi:

```text
100+
users
```

dan puluhan ribu claim tanpa redesign fundamental.

## NFR-004 — Availability

Aplikasi online harus dapat digunakan selama server dan network tersedia.

## NFR-005 — Maintainability

Codebase harus menggunakan separation of concerns:

```text
Controller
Service / Action
Policy
Request Validation
Model
Repository bila diperlukan
```

Business logic tidak boleh menumpuk di Controller.

## NFR-006 — Auditability

Aktivitas penting harus dapat ditelusuri.

## NFR-007 — Mobile First

UI harus usable pada:

* smartphone;
* tablet;
* desktop.

---

# 35. Data Integrity Requirements

System harus menjaga invariant berikut.

## DI-001

Claim total harus konsisten dengan receipt totals.

```text
Claim Total = SUM(Receipt Amount)
```

## DI-002

Claim receipt harus berasal dari requester yang sama.

## DI-003

Claim PAID tidak boleh dibayar dua kali.

## DI-004

Health utilization tidak boleh melampaui policy limit.

## DI-005

Approval tidak boleh dilakukan oleh actor tanpa permission.

## DI-006

Status transition hanya boleh terjadi melalui workflow yang valid.

## DI-007

History tidak boleh diubah secara bebas.

## DI-008

Payment tidak boleh dilakukan sebelum approval requirement terpenuhi.

## DI-009

Standard BBM yang digunakan pada transaksi harus dapat ditelusuri.

---

# 36. Error Handling

System harus menggunakan error yang jelas.

Contoh:

```text
400
Validation Error

401
Unauthenticated

403
Forbidden

404
Resource Not Found

409
Business Conflict

422
Business Validation Error

500
Internal Server Error
```

Frontend tidak boleh mengasumsikan semua error sebagai validation error.

---

# 37. Idempotency

Action sensitif harus mempertimbangkan duplicate request.

Terutama:

```text
Submit Claim
Approve Claim
Payment
```

Contoh user melakukan double-click:

```text
APPROVE
APPROVE
```

System tidak boleh menghasilkan dua approval atau dua payment.

---

# 38. Concurrency

System harus aman terhadap request bersamaan.

Contoh:

```text
User A → Approve
User B → Approve
```

Jika hanya satu approval yang valid, request kedua harus mendapatkan business response yang sesuai.

Khusus payment:

> Sistem harus menjamin satu claim tidak dapat diproses sebagai payment dua kali.

---

# 39. Document Security

Dokumen claim merupakan data internal.

Requirement:

1. Tidak public.
2. Tidak dapat diakses berdasarkan tebakan URL.
3. Authorization dilakukan server-side.
4. Download dicatat pada audit trail untuk dokumen sensitif.
5. File upload harus divalidasi.
6. Filename user tidak boleh menjadi path storage langsung.

---

# 40. Traceability Matrix

Setiap requirement harus nantinya memiliki hubungan:

```text
BRD
 ↓
PRD
 ↓
Workflow
 ↓
Database
 ↓
API
 ↓
UI
 ↓
Test
```

Contoh:

```text
BRD Business Objective
        ↓
FR-CLAIM-001
        ↓
claims table
        ↓
POST /claims
        ↓
Create Claim Page
        ↓
TC-CLAIM-001
```

---

# 41. Acceptance Criteria Global

Sebuah feature tidak dianggap selesai hanya karena:

```text
UI sudah ada
```

atau:

```text
API sudah response 200
```

Feature dianggap selesai apabila:

```text
Requirement
   +
Business Rule
   +
Authorization
   +
Validation
   +
Database Integrity
   +
Audit
   +
Error Handling
   +
Test
```

telah terpenuhi.

---

# 42. Definition of Done

Setiap functional requirement dianggap **DONE** apabila:

* backend implementation selesai;
* frontend implementation selesai;
* validation selesai;
* authorization selesai;
* database constraint/index sesuai;
* audit/history sesuai;
* error handling sesuai;
* test tersedia;
* acceptance criteria terpenuhi;
* tidak terdapat regression pada workflow terkait.

---

# 43. Future Requirements

## Excel Export

Sistem akan mendukung export:

* claim;
* payment;
* SLA;
* BBM;
* health;
* historical report.

## Document Import

Upload softcopy nota tetap menjadi mekanisme utama.

Excel import bukan prioritas versi pertama.

---

# 44. PRD Requirement Summary

| ID            | Requirement             | Priority |
| ------------- | ----------------------- | -------- |
| FR-CLAIM-001  | Create Claim            | MUST     |
| FR-CLAIM-002  | Multi Receipt           | MUST     |
| FR-CLAIM-003  | Upload Document         | MUST     |
| FR-CLAIM-004  | Submit Claim            | MUST     |
| FR-WF-001     | EDP Adm Review          | MUST     |
| FR-WF-002     | Supervisor Verification | MUST     |
| FR-WF-003     | Manager Verification    | MUST     |
| FR-WF-004     | Finance Verification    | MUST     |
| FR-WF-005     | DBM Approval            | MUST     |
| FR-WF-006     | BM Approval             | MUST     |
| FR-WF-007     | Payment                 | MUST     |
| FR-SLA-001    | SLA Calculation         | MUST     |
| FR-SLA-002    | SLA Breach              | MUST     |
| FR-SLA-003    | Historical Average      | MUST     |
| FR-BBM-001    | Vehicle Standard        | MUST     |
| FR-BBM-002    | Actual KM/L             | MUST     |
| FR-BBM-003    | Standard Comparison     | MUST     |
| FR-HEALTH-001 | Annual Limit            | MUST     |
| FR-HEALTH-002 | Utilization             | MUST     |
| FR-HEALTH-003 | Remaining Limit         | MUST     |
| FR-AUDIT-001  | Claim History           | MUST     |
| FR-AUDIT-002  | Audit Trail             | MUST     |

---

# 45. PRD Status

**Version:** 1.0
**Status:** Baseline

PRD ini menjadi input untuk fase berikutnya:

```text
PRD v1.0
   ↓
BUSINESS PROCESS
   ↓
WORKFLOW / STATE MACHINE
   ↓
DOMAIN MODEL
   ↓
DATABASE_SCHEMA.md
   ↓
DATA_DICTIONARY.md
   ↓
API_SPECIFICATION.md
   ↓
UI/UX
   ↓
TEST PLAN
```

---

# 46. Important Open Decisions

Requirement berikut masih harus ditentukan sebelum database final:

1. Formula detail reimbursement BBM.
2. Sumber data `distance_km`.
3. Apakah kendaraan menggunakan master vehicle.
4. Apakah satu user dapat memiliki lebih dari satu kendaraan.
5. Kapan health limit dianggap terpakai.
6. Toleransi BBM terhadap standard.
7. Definisi `AT_RISK`.
8. Hari kerja untuk SLA.
9. Jam kerja untuk SLA.
10. Hari libur untuk SLA.
11. Apakah DBM dan BM selalu sequential atau configurable per policy.
12. Apakah approval dapat di-delegate.
13. Apakah requester boleh cancel claim.
14. Apakah paid claim dapat dikoreksi melalui adjustment.
15. Aturan final duplicate claim.
16. Retention dokumen.
17. Retention audit log.
18. Format nomor claim.

---

# 47. Next Phase

Setelah PRD v1.0 ini disepakati, tahap berikutnya adalah:

**PHASE 3 — BUSINESS PROCESS & WORKFLOW DESIGN**

Dokumen yang akan dibuat:

```text
04_BUSINESS_PROCESS.md
05_USER_ROLES.md
06_WORKFLOW.md
```

Fokus fase tersebut adalah menentukan secara presisi:

```text
WHO
 ↓
CAN DO WHAT
 ↓
UNDER WHAT CONDITION
 ↓
TO WHICH STATUS
 ↓
WHAT HAPPENS NEXT
```

Setelah workflow terkunci, baru kita masuk ke:

**PHASE 4 — DOMAIN MODEL & DATABASE DESIGN**

yang akan menghasilkan:

```text
DATABASE_SCHEMA.md
DATA_DICTIONARY.md
DATABASE_INDEXING.md
ERD
```

sehingga struktur database dibuat berdasarkan **workflow yang sudah pasti**, bukan berdasarkan asumsi developer.
