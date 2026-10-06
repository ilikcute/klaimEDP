# REPORTING SPECIFICATION

## Sistem Rekap, Tracking & Analisa Klaim / Reimbursement EDP

**Version:** 1.0

---

# 1. Tujuan

Reporting menyediakan informasi untuk:

* operasional;
* monitoring;
* finance;
* management;
* audit;
* historical analysis.

---

# 2. Claim Summary Report

Filter:

```text
periode
claim type
requester
status
department
```

Output:

```text
Claim Number
Requester
Type
Date
Total
Status
Current Stage
SLA
```

---

# 3. Claim Detail Report

Menampilkan:

* claim;
* requester;
* receipts;
* documents;
* workflow;
* approval;
* payment;
* audit timeline.

---

# 4. Claim by Type

Kategori:

```text
BBM
TOL
HEALTH
```

Metric:

```text
jumlah claim
total amount
average amount
percentage
```

---

# 5. Historical Trend

Period:

```text
Daily
Weekly
Monthly
Quarterly
Yearly
```

Metric:

```text
Claim Count
Claim Amount
Paid Amount
Average Claim
```

---

# 6. SLA Report

Metric:

```text
Average Processing Time
Average Stage Time
On Time %
At Risk %
Breached %
```

Breakdown:

```text
Claim Type
Stage
Period
User
Role
```

---

# 7. SLA Stage Analysis

Tahapan:

```text
EDP Review
Supervisor Verification
Manager Verification
Finance Verification
Management Approval
Payment
```

Report:

```text
Average
Minimum
Maximum
Median
P90
```

P90 sangat berguna untuk melihat tail latency proses.

---

# 8. Management Approval Report

Karena policy:

```text
DBM OR BM
```

report harus membedakan:

```text
Approved by DBM
Approved by BM
```

Metric:

```text
Count
Amount
Average approval time
```

---

# 9. BBM Report

Metric:

```text
Total KM
Total Liter
Average KM/L
Standard KM/L
Variance
Compliance
```

Grouping:

```text
Vehicle Type
Vehicle
Requester
Period
```

---

# 10. BBM Compliance

Kategori:

```text
COMPLIANT
BELOW_STANDARD
ABOVE_STANDARD
```

Dashboard:

```text
Compliance %
Below Standard %
Above Standard %
```

---

# 11. Health Report

Metric:

```text
Annual Limit
Paid
Pending
Approved
Remaining
Utilization %
```

Grouping:

```text
User
Year
Department
```

---

# 12. Health Limit Alert

System dapat memberikan warning:

```text
80%
90%
100%
```

Threshold configurable.

Contoh:

```text
User A
Limit     Rp10.000.000
Paid      Rp8.900.000
Usage     89%
Remaining Rp1.100.000
```

---

# 13. Payment Report

Filter:

```text
payment date
bank
requester
claim type
status
```

Output:

```text
Claim
Requester
Approved Amount
Paid Amount
Payment Date
Bank
Reference
Processed By
```

---

# 14. Outstanding Payment

Menampilkan:

```text
APPROVED
PAYMENT_PROCESS
PAYMENT_FAILED
```

Metric:

```text
Count
Total Amount
Age
```

---

# 15. Aging Report

Claim aging:

```text
0–1 day
2–3 days
4–7 days
8–14 days
>14 days
```

Digunakan untuk management monitoring.

---

# 16. Workflow Bottleneck

Report harus dapat menemukan:

```text
Stage dengan average duration tertinggi
Stage dengan breach tertinggi
Stage dengan queue terbesar
```

Contoh:

```text
Finance Verification
Average = 2.4 days
Breach = 18%
```

---

# 17. Historical Average

System menghitung:

```text
Average Processing Time
```

Formula:

```text
sum(completed duration)
/
number of completed claims
```

Namun untuk analisa yang lebih baik juga tersedia:

```text
Median
P90
```

karena average dapat terdistorsi oleh outlier.

---

# 18. Duplicate Claim Report

Potential duplicate:

```text
Requester
Claim Type
Transaction Date
Receipt Number
Merchant
Amount
```

Report:

```text
Potential Duplicate
Reviewed
Confirmed Duplicate
Cleared
```

Duplicate detection bersifat advisory.

---

# 19. Dashboard KPI

Management dashboard minimal:

```text
Total Claims
Total Amount
Paid Amount
Pending Claims
Pending Approval
SLA At Risk
SLA Breached
Average Processing Time
```

---

# 20. Monthly Dashboard

```text
                    MONTH
                      │
        ┌─────────────┼─────────────┐
        ▼             ▼             ▼
    Claim Count    Claim Amount   Paid Amount
        │             │             │
        └─────────────┼─────────────┘
                      ▼
                 Trend Analysis
```

---

# 21. Report Export

Format awal:

```text
CSV
```

Future:

```text
XLSX
PDF
```

Export harus:

* respect authorization;
* respect filters;
* audit logged;
* asynchronous untuk data besar.

---

# 22. Export Audit

Setiap export:

```text
actor
report
filter
timestamp
format
record_count
```

dicatat pada audit.

---

# 23. Reporting Performance

Report besar tidak boleh menjalankan:

```text
N+1 query
```

Gunakan:

* aggregate SQL;
* proper indexes;
* pagination;
* date range;
* pre-aggregation bila diperlukan.

---

# 24. Dashboard Performance

Dashboard harus menghindari query terpisah untuk setiap card.

Prefer:

```text
aggregated queries
```

atau reporting/read model bila volume meningkat.

---

# 25. Data Consistency

Reporting harus menggunakan database transactional state.

Contoh:

```text
Paid Amount
```

berasal dari payment records dengan:

```text
status = PAID
```

bukan dari angka yang dikirim frontend.

---

# 26. Report Security

User hanya melihat data sesuai scope.

Contoh:

```text
EDP Operasional
→ own claims

EDP Manager
→ management scope

Finance
→ finance scope

Admin
→ global
```

---

# 27. Recommended Analytics Layer

Untuk fase awal:

```text
Transactional DB
        ↓
SQL Aggregation
        ↓
Reporting API
        ↓
Dashboard
```

Jika volume meningkat:

```text
Transactional DB
        ↓
Read Model / Summary Tables
        ↓
Analytics API
```

Tidak perlu data warehouse pada fase awal.

---

# 28. Reporting Definition of Done

* [ ] Claim report.
* [ ] Payment report.
* [ ] SLA report.
* [ ] BBM report.
* [ ] Health report.
* [ ] Historical report.
* [ ] Aging report.
* [ ] Bottleneck analysis.
* [ ] Management approval report.
* [ ] Duplicate report.
* [ ] Dashboard KPI.
* [ ] Export.
* [ ] Export audit.
* [ ] Authorization.
* [ ] Pagination.
* [ ] Query optimization.

---

**End of REPORTING_SPEC.md**
