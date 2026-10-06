# BUSINESS PROCESS

## Sistem Rekap, Tracking & Analisa Klaim / Reimbursement EDP

**Version:** 1.0
**Status:** Draft for Architecture & Development
**Platform:** Web Application
**Backend:** Laravel
**Frontend:** Vue.js
**Database:** MySQL
**Authentication:** Laravel Sanctum
**Integration:** Telegram Bot

---

# 1. Tujuan Dokumen

Dokumen ini mendefinisikan proses bisnis end-to-end untuk pengelolaan klaim/reimbursement EDP, mulai dari pembuatan klaim, pengumpulan nota/dokumen, pemeriksaan, verifikasi, approval, pembayaran, hingga histori dan analisa.

Dokumen ini menjadi acuan utama untuk:

* desain workflow;
* desain role dan authorization;
* desain database;
* desain API;
* desain UI/UX;
* implementasi notification;
* implementasi SLA;
* reporting dan dashboard;
* audit trail.

---

# 2. Tujuan Sistem

Sistem dibangun untuk:

1. Memusatkan seluruh proses reimbursement EDP.
2. Mengurangi proses manual dan kehilangan dokumen.
3. Memastikan setiap klaim memiliki histori yang jelas.
4. Memastikan klaim melewati tahapan pemeriksaan yang sesuai.
5. Memudahkan tracking posisi klaim.
6. Mengukur lama proses setiap tahapan.
7. Menganalisis pola reimbursement historis.
8. Menjaga integritas data dan mencegah manipulasi.
9. Menyediakan informasi yang mudah digunakan oleh management.
10. Menyediakan audit trail untuk kebutuhan pemeriksaan.

---

# 3. Jenis Klaim

Sistem mendukung tiga jenis klaim utama.

| Kode   | Jenis              | Karakteristik                       |
| ------ | ------------------ | ----------------------------------- |
| BBM    | Bahan Bakar Minyak | Memiliki standar konsumsi kendaraan |
| TOL    | Tol                | Tidak memiliki rasio konsumsi       |
| HEALTH | Kesehatan          | Memiliki batas tahunan per user     |

---

# 4. Aktor Bisnis

Aktor utama:

1. EDP Operasional
2. EDP Adm
3. EDP Supervisor
4. EDP Manager
5. GA/Finance
6. DBM
7. BM
8. Admin Sistem

Finance juga berperan dalam proses pembayaran setelah approval selesai.

---

# 5. Prinsip Dasar Proses Bisnis

## 5.1 Claim Owner

Setiap klaim memiliki satu pemilik/requester.

Satu klaim dapat memiliki banyak nota, tetapi seluruh nota dalam satu klaim harus:

* berasal dari requester yang sama;
* terkait dengan jenis klaim yang sama;
* berada dalam periode klaim yang valid.

Sistem tidak boleh memungkinkan nota milik personel lain dimasukkan ke dalam klaim.

---

## 5.2 Total Klaim

Total klaim tidak boleh menjadi nilai yang dipercaya dari frontend.

Formula:

```text
Claim Total = SUM(Receipt Amount)
```

Perhitungan harus dilakukan server-side.

---

## 5.3 Dokumen

Dokumen dapat berupa:

* foto;
* scan;
* PDF;
* dokumen pendukung lainnya;
* bukti pembayaran.

Dokumen fisik dapat diterima kemudian.

Status dokumen dapat berupa:

```text
PENDING
RECEIVED
VERIFIED
REJECTED
ARCHIVED
```

---

# 6. Proses Bisnis End-to-End

Proses utama:

```text
CREATE CLAIM
     ↓
ADD RECEIPTS
     ↓
UPLOAD DOCUMENTS
     ↓
SUBMIT
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

Dengan kemungkinan cabang:

```text
                     ┌── REQUEST REVISION ──┐
                     │                      ↓
SUBMITTED → REVIEW → VERIFY → APPROVAL → kembali ke requester
                     │
                     └── REJECTED
```

---

# 7. Proses 1 — Create Claim

Requester atau role yang memiliki hak input membuat klaim.

Data minimum:

* requester;
* claim type;
* periode/tanggal;
* keterangan;
* nota;
* dokumen pendukung.

Status awal:

```text
DRAFT
```

Pada tahap ini klaim belum masuk proses approval.

---

# 8. Proses 2 — Add Receipt

User dapat menambahkan satu atau banyak nota.

Contoh:

```text
Claim #CLM-2026-00001

Receipt 1 = Rp100.000
Receipt 2 = Rp150.000
Receipt 3 = Rp200.000

Total = Rp450.000
```

Sistem menghitung total secara otomatis.

User tidak diperbolehkan menentukan total klaim secara manual sebagai sumber kebenaran.

---

# 9. Proses 3 — Upload Document

User mengunggah softcopy dokumen.

Validasi minimal:

* MIME type;
* extension;
* ukuran file;
* ownership;
* authorization;
* malware/security policy apabila tersedia.

File disimpan pada private storage.

Akses file menggunakan authorization, bukan URL publik bebas.

---

# 10. Proses 4 — Submit Claim

Sebelum submit sistem melakukan validation gate.

Minimal:

* requester valid;
* claim type valid;
* minimal satu receipt;
* receipt valid;
* total > 0;
* dokumen wajib tersedia;
* aturan BBM/Health terpenuhi;
* tidak ada blocking validation;
* user memiliki hak submit.

Jika valid:

```text
DRAFT → SUBMITTED
```

Submit menghasilkan:

* status history;
* audit trail;
* timestamp;
* actor;
* notification.

---

# 11. Proses 5 — EDP Adm Review

EDP Adm melakukan pemeriksaan administratif awal.

Fokus:

* kelengkapan data;
* kelengkapan nota;
* kelengkapan dokumen;
* kesesuaian requester;
* format data;
* periode klaim.

EDP Adm **bukan approval akhir**.

EDP Adm dapat:

```text
FORWARD
REQUEST_REVISION
REJECT
```

---

# 12. Proses 6 — Supervisor Verification

Supervisor melakukan verifikasi substantif.

Fokus:

* kebenaran requester;
* kesesuaian transaksi;
* nota;
* nominal;
* dokumen;
* jenis klaim;
* aturan reimbursement;
* BBM standard;
* Health limit.

Hasil:

```text
VERIFIED
REQUEST_REVISION
REJECTED
```

---

# 13. Proses 7 — Manager Verification

Manager melakukan pemeriksaan lanjutan.

Fokus:

* hasil verifikasi Supervisor;
* kewajaran klaim;
* compliance;
* histori klaim;
* risiko;
* kelengkapan evidence.

Hasil:

```text
VERIFIED
REQUEST_REVISION
REJECTED
```

---

# 14. Proses 8 — Finance Verification

GA/Finance melakukan final verification sebelum approval management.

Fokus:

* nominal;
* kelengkapan dokumen;
* validitas reimbursement;
* hasil verification;
* kesiapan pembayaran;
* data rekening;
* potensi duplicate claim;
* compliance finance.

Hasil:

```text
VERIFIED
REQUEST_REVISION
REJECTED
```

---

# 15. Proses 9 — DBM Approval

Setelah final verification:

```text
FINANCE VERIFIED
       ↓
DBM APPROVAL
```

DBM melakukan approval management.

Keputusan:

```text
APPROVED
REJECTED
REQUEST_REVISION
```

Urutan default:

```text
DBM → BM
```

Tetapi konfigurasi sistem harus memungkinkan kebijakan:

```text
DBM AND BM
```

atau:

```text
DBM OR BM
```

---

# 16. Proses 10 — BM Approval

BM melakukan approval akhir sesuai policy.

Default:

```text
DBM APPROVED
      ↓
BM APPROVAL
      ↓
APPROVED
```

Setelah BM approve, klaim dapat masuk proses pembayaran.

---

# 17. Proses 11 — Payment

Finance memproses pembayaran.

Metode pembayaran:

```text
BANK TRANSFER
```

Informasi pembayaran minimal:

* payment date;
* payment amount;
* bank;
* account number;
* account name;
* transfer/reference number;
* payment proof;
* processed by.

Status:

```text
APPROVED
   ↓
PAYMENT_PROCESS
   ↓
PAID
```

Sistem harus mencegah:

* pembayaran ganda;
* nominal pembayaran melebihi approved amount;
* pembayaran tanpa approval;
* pembayaran oleh user tanpa permission.

---

# 18. Revision

Revision digunakan apabila klaim masih dapat diperbaiki.

Contoh alasan:

* nota kurang jelas;
* dokumen kurang;
* nominal tidak sesuai;
* data kendaraan salah;
* data rekening salah;
* informasi belum lengkap.

Revision harus menyimpan:

* actor;
* timestamp;
* stage;
* reason;
* notes;
* previous status;
* target status.

Revision tidak boleh menghapus histori sebelumnya.

---

# 19. Rejection

Reject berarti klaim tidak dapat dilanjutkan dalam workflow tersebut.

Alasan reject wajib dicatat.

Contoh:

```text
NOT_ELIGIBLE
INVALID_DOCUMENT
DUPLICATE_CLAIM
INVALID_AMOUNT
POLICY_VIOLATION
INSUFFICIENT_EVIDENCE
OTHER
```

Duplicate detection bersifat advisory.

Sistem memberikan warning, tetapi keputusan final tetap pada authorized reviewer.

---

# 20. BBM Business Process

## Standard

Default:

```text
Motor = 40 KM/L
Mobil = 13 KM/L
```

Standard harus dapat dikonfigurasi dan memiliki effective date.

---

## Actual KM/L

Formula:

```text
Actual KM/L = Distance KM / Fuel Liter
```

Contoh:

```text
Distance = 400 KM
Fuel = 10 Liter

Actual = 400 / 10
       = 40 KM/L
```

---

## Comparison

Sistem membandingkan:

```text
Actual KM/L
vs
Standard KM/L
```

Hasil dapat berupa:

```text
COMPLIANT
BELOW_STANDARD
ABOVE_STANDARD
```

Tolerance dapat dikonfigurasi.

Historical claim harus menggunakan standard yang berlaku pada saat transaksi, bukan standard terbaru.

---

# 21. TOL Business Process

TOL tidak memiliki rasio konsumsi.

Fokus pada:

* tanggal;
* lokasi;
* transaksi;
* nominal;
* receipt;
* dokumen pendukung.

Tidak dilakukan kalkulasi KM/L.

---

# 22. Health Business Process

Default annual limit:

```text
Rp10.000.000 / user / year
```

Sistem harus menghitung:

```text
Annual Limit
-
Paid Utilization
=
Remaining Limit
```

Dashboard dapat menampilkan:

```text
Limit
Pending
Approved
Paid
Remaining
```

Basis final utilization sebaiknya menggunakan klaim `PAID`, sedangkan pending/approved tetap ditampilkan sebagai exposure.

---

# 23. SLA Business Process

Default SLA:

```text
3 working days
```

SLA dihitung berdasarkan konfigurasi:

* working days;
* weekend;
* holiday;
* working hours;
* timezone;
* SLA per workflow stage.

Status:

```text
ON_TIME
AT_RISK
BREACHED
```

---

# 24. Duplicate Detection

Sistem melakukan potential duplicate detection berdasarkan kombinasi data seperti:

```text
Requester
+
Claim Type
+
Transaction Date
+
Receipt Number
+
Merchant
+
Amount
```

Hasil:

```text
NO_DUPLICATE
POTENTIAL_DUPLICATE
```

Potential duplicate tidak otomatis ditolak.

Reviewer yang berwenang menentukan keputusan.

---

# 25. Notification

Telegram dapat digunakan untuk event:

* claim submitted;
* revision requested;
* claim verified;
* claim approved;
* claim rejected;
* payment completed;
* SLA at risk;
* SLA breached.

Notification tidak boleh menjadi sumber kebenaran workflow.

Database tetap menjadi source of truth.

---

# 26. History & Audit

Setiap perubahan penting menghasilkan:

### Claim History

Mencatat perubahan status.

### Audit Trail

Mencatat aktivitas sistem/user.

Contoh:

```text
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
```

Audit trail bersifat append-only.

---

# 27. Business Rules Utama

| Rule   | Ketentuan                                                                   |
| ------ | --------------------------------------------------------------------------- |
| BR-001 | Satu claim memiliki satu requester                                          |
| BR-002 | Satu claim dapat memiliki banyak receipt                                    |
| BR-003 | Receipt harus milik requester yang sama                                     |
| BR-004 | Claim total dihitung server-side                                            |
| BR-005 | Claim harus memiliki minimal satu receipt                                   |
| BR-006 | Approval tidak boleh dilakukan sebelum verification yang diwajibkan selesai |
| BR-007 | Payment tidak boleh dilakukan sebelum final approval                        |
| BR-008 | Payment tidak boleh double                                                  |
| BR-009 | History tidak boleh dihapus                                                 |
| BR-010 | Audit trail tidak boleh dihapus oleh user biasa                             |
| BR-011 | Health limit dihitung per user per tahun                                    |
| BR-012 | BBM standard harus effective-dated                                          |
| BR-013 | Duplicate detection bersifat advisory                                       |
| BR-014 | Semua workflow action harus authorized server-side                          |
| BR-015 | Dokumen private harus dilindungi authorization                              |

---

# 28. Business Process Principle

Prinsip utama:

> **Data → Evidence → Verification → Approval → Payment → History → Analysis**

Tidak boleh ada proses:

```text
Approval → Payment
```

tanpa verification dan evidence yang dipersyaratkan.

Tidak boleh ada:

```text
Payment
```

tanpa approval yang valid.

---

# 29. Source of Truth

| Data         | Source of Truth                         |
| ------------ | --------------------------------------- |
| Claim status | Database                                |
| Claim total  | Receipt records                         |
| Workflow     | Workflow state/action records           |
| History      | Status history                          |
| Audit        | Audit trail                             |
| Payment      | Payment records                         |
| Documents    | Private storage + document metadata     |
| Notification | Telegram hanya sebagai delivery channel |
| SLA          | Workflow timestamps + SLA configuration |

---

# 30. End-to-End Summary

```text
EDP OPERASIONAL / EDP ADM
        │
        ▼
     CREATE
        │
        ▼
   ADD RECEIPTS
        │
        ▼
 UPLOAD DOCUMENTS
        │
        ▼
      SUBMIT
        │
        ▼
   EDP ADM REVIEW
        │
        ├── REVISION ──────┐
        │                   │
        ▼                   │
 SUPERVISOR VERIFY          │
        │                   │
        ├── REVISION ──────┤
        │                   │
        ▼                   │
  MANAGER VERIFY            │
        │                   │
        ├── REVISION ──────┤
        │                   │
        ▼                   │
 FINANCE VERIFY             │
        │                   │
        ├── REVISION ──────┤
        │                   │
        ▼                   │
   DBM APPROVAL             │
        │                   │
        ├── REVISION ──────┤
        │                   │
        ▼                   │
    BM APPROVAL             │
        │                   │
        ▼                   │
      APPROVED              │
        │                   │
        ▼                   │
 PAYMENT PROCESS            │
        │                   │
        ▼                   │
        PAID                │
        │                   │
        ▼                   │
     ANALYTICS              │
                            │
        ◄───────────────────┘
```

---

# 31. Dokumen Terkait

Dokumen ini harus dibaca bersama:

* `BRD.md`
* `PRD.md`
* `USER_ROLES.md`
* `WORKFLOW.md`
* `DATABASE_SCHEMA.md`
* `API_SPEC.md`
* `UI_UX_SPEC.md`
* `SECURITY_SPEC.md`
* `REPORTING_SPEC.md`

---

**End of BUSINESS_PROCESS.md**
