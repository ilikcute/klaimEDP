# UI / UX SPECIFICATION

## Sistem Rekap, Tracking & Analisa Klaim / Reimbursement EDP

**Version:** 1.0
**Design:** Mobile-first
**Frontend:** Vue.js

---

# 1. UX Objective

UI harus membuat user dapat:

* membuat claim dengan cepat;
* memahami posisi claim;
* mengetahui action yang harus dilakukan;
* melihat dokumen;
* melakukan verification;
* melakukan approval;
* mengetahui SLA;
* mengetahui histori.

---

# 2. Navigation

Mobile:

```text
Dashboard
Claims
Create Claim
Tasks
Notifications
Profile
```

Desktop:

```text
Sidebar
 ├── Dashboard
 ├── Claims
 ├── My Claims
 ├── Tasks
 ├── Reports
 ├── Analytics
 ├── Master Data
 └── Administration
```

Menu harus role-aware.

---

# 3. Dashboard

Dashboard harus menampilkan KPI yang relevan dengan role.

### Requester

```text
Draft
Submitted
Revision
Approved
Paid
Total Claimed
```

### Reviewer

```text
Waiting Review
Waiting Verification
SLA At Risk
SLA Breached
```

### Finance

```text
Finance Queue
Ready Payment
Paid
Payment Failed
```

### Management

```text
Pending Approval
Total Claim
Claim by Type
SLA
Historical Trend
```

---

# 4. Create Claim

Wizard mobile-first:

```text
1. Claim Type
2. Claim Information
3. Receipt
4. Documents
5. Review
6. Submit
```

Progress indicator wajib.

---

# 5. Claim Type Selection

Gunakan card:

```text
┌─────────────┐
│    BBM      │
│ Bahan Bakar │
└─────────────┘

┌─────────────┐
│    TOL      │
│    Tol      │
└─────────────┘

┌─────────────┐
│   HEALTH    │
│  Kesehatan  │
└─────────────┘
```

Setelah type dipilih, form menyesuaikan.

---

# 6. Receipt Form

Receipt harus mudah ditambahkan:

```text
+ Tambah Nota
```

Setiap receipt berupa card.

```text
Merchant
Tanggal
Nomor Nota
Nominal
Keterangan
```

Total selalu terlihat:

```text
TOTAL KLAIM
Rp xxx.xxx
```

---

# 7. BBM UI

Field:

```text
Vehicle
Distance KM
Fuel Liter
```

System displays:

```text
Actual KM/L
Standard KM/L
Variance
Status
```

Contoh:

```text
Actual       38.5 KM/L
Standard     40.0 KM/L
Variance     -1.5 KM/L
Status       Below Standard
```

Nilai standard readonly.

---

# 8. Health UI

Tampilkan:

```text
Annual Limit
Paid
Pending
Approved
Remaining
```

Visual utilization:

```text
████████░░ 70%
```

Jangan hanya menampilkan remaining tanpa konteks.

---

# 9. Document Upload

Support:

* camera/mobile capture;
* gallery;
* file picker.

Tampilkan:

```text
filename
size
type
upload status
verification status
```

Upload harus mendukung retry.

---

# 10. Claim Detail

Layout:

```text
Claim Header
      ↓
Status / SLA
      ↓
Financial Summary
      ↓
Receipts
      ↓
Documents
      ↓
BBM/Health Details
      ↓
Workflow Timeline
      ↓
Payment
```

---

# 11. Status Badge

Gunakan semantic status.

Contoh:

```text
DRAFT
SUBMITTED
IN REVIEW
IN VERIFICATION
WAITING APPROVAL
APPROVED
PAYMENT
PAID
REVISION
REJECTED
```

Jangan hanya mengandalkan warna.

---

# 12. Workflow Timeline

Timeline:

```text
✓ Submitted
│
✓ EDP Review
│
✓ Supervisor Verification
│
● Manager Verification
│
○ Finance Verification
│
○ Management Approval
│
○ Payment
```

Management approval ditampilkan:

```text
DBM / BM
```

karena salah satu wajib.

---

# 13. Action Panel

Action button hanya tampil jika:

```text
permission
+
state
+
policy
```

memungkinkan.

Contoh Supervisor:

```text
[ VERIFY ]
[ REQUEST REVISION ]
[ REJECT ]
```

BM/DBM:

```text
[ APPROVE ]
[ REQUEST REVISION ]
[ REJECT ]
```

---

# 14. Revision UI

Revision form wajib:

```text
Reason
Notes
```

Tampilkan siapa yang meminta revision.

---

# 15. Rejection UI

Reject harus menggunakan confirmation modal:

```text
Reject Claim?

Reason *
Notes *

[Cancel] [Reject]
```

Tidak boleh reject hanya dengan satu accidental click.

---

# 16. Approval UI

Approval screen menampilkan:

```text
Requester
Claim Type
Total
Receipts
Documents
Verification History
Previous Approvals
Potential Duplicate Warning
SLA
```

Approver tidak boleh dipaksa approve tanpa melihat evidence.

---

# 17. Payment UI

Finance melihat:

```text
Approved Amount
Payment Amount
Bank
Account
Reference
Proof
```

System validation:

```text
Payment Amount <= Approved Amount
```

Button:

```text
[ Process Payment ]
```

Setelah sukses:

```text
PAID
```

Button payment tidak boleh tersedia lagi.

---

# 18. Mobile UX

Minimum target:

```text
360px width
```

Touch target:

```text
≥ 44px
```

Form:

* single column;
* large input;
* numeric keyboard untuk nominal;
* date picker;
* camera upload;
* sticky primary action.

---

# 19. Desktop UX

Desktop memanfaatkan:

```text
2-column
3-column
data table
side panel
```

tetapi tidak boleh mengorbankan readability.

---

# 20. Accessibility

Target:

```text
WCAG 2.1 AA
```

Minimal:

* keyboard accessible;
* visible focus;
* sufficient contrast;
* labels;
* error messages;
* semantic HTML;
* no color-only status;
* screen-reader friendly.

---

# 21. Loading State

Jangan menggunakan blank screen.

Gunakan:

```text
Skeleton
Spinner
Progress indicator
```

Untuk payment/approval gunakan explicit processing state.

---

# 22. Double Submission

Critical action harus disable selama request:

```text
Submitting...
Processing...
Approving...
```

Backend tetap wajib idempotent.

---

# 23. Error UX

Error harus actionable.

Buruk:

```text
Error 422
```

Baik:

```text
Nomor rekening belum diisi.
Silakan lengkapi data pembayaran.
```

Untuk conflict:

```text
Claim sudah diproses user lain.
Silakan refresh halaman untuk melihat status terbaru.
```

---

# 24. Responsive Tables

Tabel claim pada mobile jangan dipaksakan menjadi desktop table.

Gunakan:

```text
Card/List View
```

Desktop:

```text
Data Table
```

---

# 25. Notification Center

Notification:

```text
Claim Submitted
Revision Required
Verification Completed
Approval Completed
Payment Completed
SLA Warning
```

Unread count ditampilkan.

---

# 26. Search & Filter

Filter:

```text
Claim Number
Claim Type
Status
Requester
Date
Amount
SLA
```

Mobile menggunakan filter drawer/bottom sheet.

---

# 27. Reports UI

Report harus menyediakan:

```text
Filter
Summary
Table
Chart
Export
```

Jangan menampilkan chart tanpa angka summary.

---

# 28. Design System

Gunakan semantic tokens:

```text
primary
secondary
success
warning
danger
info
surface
background
text
muted
border
```

Status tidak boleh hard-coded per component.

---

# 29. UX Principle

Prioritas:

```text
Clarity
↓
Accuracy
↓
Speed
↓
Consistency
↓
Visual Polish
```

Untuk aplikasi reimbursement, **akurasi dan traceability lebih penting daripada dekorasi visual**.

---

# 30. UI Definition of Done

* [ ] Mobile-first.
* [ ] Responsive.
* [ ] Accessible.
* [ ] Role-aware.
* [ ] State-aware.
* [ ] Loading state.
* [ ] Error state.
* [ ] Empty state.
* [ ] Confirmation action.
* [ ] Timeline.
* [ ] Document preview.
* [ ] Receipt management.
* [ ] BBM visualization.
* [ ] Health utilization.
* [ ] Payment UI.
* [ ] Dashboard.
* [ ] Reports.

---

**End of UI_UX_SPEC.md**
