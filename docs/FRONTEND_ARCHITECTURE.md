# FRONTEND ARCHITECTURE

## Sistem Rekap, Tracking & Analisa Klaim / Reimbursement EDP

**Version:** 1.0
**Frontend:** Vue.js
**Design:** Mobile-first

---

# 1. Frontend Architecture

```text
Vue App
   │
   ├── Pages
   ├── Components
   ├── Composables
   ├── Stores
   ├── API Client
   ├── Permissions
   └── Utilities
          │
          ▼
       Laravel API
```

---

# 2. Recommended Structure

```text
resources/js/
├── app/
│   ├── router/
│   ├── providers/
│   └── bootstrap.ts
│
├── pages/
│   ├── auth/
│   ├── dashboard/
│   ├── claims/
│   ├── tasks/
│   ├── reports/
│   ├── analytics/
│   └── admin/
│
├── components/
│   ├── layout/
│   ├── claims/
│   ├── receipts/
│   ├── documents/
│   ├── workflow/
│   ├── payment/
│   ├── bbm/
│   ├── health/
│   ├── dashboard/
│   └── common/
│
├── composables/
│   ├── useAuth.ts
│   ├── usePermissions.ts
│   ├── useClaims.ts
│   ├── useClaimWorkflow.ts
│   ├── useDocuments.ts
│   ├── usePagination.ts
│   ├── useFilters.ts
│   └── useNotifications.ts
│
├── stores/
│   ├── auth.ts
│   ├── claim.ts
│   ├── notification.ts
│   └── ui.ts
│
├── api/
│   ├── client.ts
│   ├── auth.ts
│   ├── claims.ts
│   ├── workflow.ts
│   ├── documents.ts
│   ├── payments.ts
│   ├── reports.ts
│   ├── bbm.ts
│   └── health.ts
│
├── types/
│   ├── claim.ts
│   ├── workflow.ts
│   ├── payment.ts
│   └── user.ts
│
└── utils/
    ├── money.ts
    ├── date.ts
    ├── validation.ts
    └── permissions.ts
```

---

# 3. Pages

Main:

```text
Login
Dashboard
My Claims
Claim Detail
Create Claim
Edit Claim
Task Queue
Reports
Analytics
Profile
Notifications
```

---

# 4. Claim Page Hierarchy

```text
ClaimPage
 ├── ClaimHeader
 │    ├── ClaimNumber
 │    ├── ClaimStatus
 │    └── SlaBadge
 │
 ├── ClaimSummary
 │
 ├── ClaimTypeSection
 │    ├── BbmDetails
 │    ├── TolDetails
 │    └── HealthDetails
 │
 ├── ReceiptList
 │    └── ReceiptCard
 │
 ├── DocumentList
 │    └── DocumentCard
 │
 ├── WorkflowTimeline
 │
 ├── WorkflowActionPanel
 │
 └── PaymentSection
```

---

# 5. Claim Creation

```text
CreateClaimPage
      │
      ├── ClaimTypeSelector
      │
      ├── ClaimForm
      │
      ├── ReceiptManager
      │
      ├── DocumentUploader
      │
      ├── ClaimPreview
      │
      └── SubmitBar
```

---

# 6. Receipt Manager

Responsibilities:

* add receipt;
* edit receipt;
* remove receipt;
* validate input;
* show subtotal;
* show total.

Frontend may calculate preview total for UX.

However:

```text
frontend total ≠ authoritative total
```

Backend recalculates.

---

# 7. Document Uploader

Component:

```text
DocumentUploader
```

Features:

* drag/drop desktop;
* camera/mobile;
* progress;
* retry;
* preview;
* remove;
* validation.

Upload response should return:

```text
document_id
status
filename
```

---

# 8. Workflow Timeline

```text
WorkflowTimeline
 ├── WorkflowStep
 ├── WorkflowActor
 ├── WorkflowTimestamp
 ├── WorkflowDecision
 └── WorkflowNotes
```

Timeline is read-only.

---

# 9. Workflow Action Panel

Backend returns allowed actions:

```json
{
  "state": "SUPERVISOR_VERIFICATION",
  "allowed_actions": [
    "VERIFY",
    "REQUEST_REVISION",
    "REJECT"
  ]
}
```

Frontend renders actions based on this.

Tetapi backend tetap melakukan authorization.

---

# 10. Permission System

Store:

```text
auth.user
auth.roles
auth.permissions
```

Composable:

```text
usePermissions()
```

API:

```text
can(permission)
hasRole(role)
```

---

# 11. Important Principle

Jangan menggunakan:

```text
if (user.role === 'BM')
```

di seluruh component.

Gunakan:

```text
can('claim.approve')
```

atau capability/action dari backend.

---

# 12. State Handling

Claim state berasal dari server.

Frontend state:

```text
server_state
+
local_form_state
+
UI_state
```

Jangan menjadikan local state sebagai source of truth untuk workflow.

---

# 13. Pinia / Store Strategy

Jika menggunakan Pinia:

```text
authStore
claimStore
notificationStore
uiStore
```

Jangan memasukkan seluruh server cache ke global store tanpa alasan.

Claim detail dapat menggunakan page-level state/query composable.

---

# 14. API Client

Central client:

```text
api/client.ts
```

Responsibilities:

* base URL;
* credentials;
* CSRF;
* headers;
* JSON;
* error normalization;
* request ID;
* idempotency key support.

---

# 15. API Modules

```text
claimsApi
workflowApi
documentsApi
paymentsApi
reportsApi
bbmApi
healthApi
```

Pages tidak melakukan raw `fetch()` di banyak tempat.

---

# 16. Composables

### useClaims

```text
listClaims()
getClaim()
createClaim()
updateClaim()
submitClaim()
```

### useClaimWorkflow

```text
getTimeline()
getAllowedActions()
executeAction()
```

### useDocuments

```text
upload()
download()
remove()
```

---

# 17. Form State

Create/edit form:

```text
claim
receipts[]
documents[]
bbm
health
```

Validation:

```text
client-side
```

tetapi backend tetap authoritative.

---

# 18. Dirty State

Jika user meninggalkan form:

```text
unsaved changes
```

berikan warning.

Draft dapat disimpan secara server-side bila diperlukan.

---

# 19. Loading State

Setiap async operation harus memiliki:

```text
idle
loading
success
error
```

Critical actions:

```text
processing
```

---

# 20. Double Click Protection

Button:

```text
Approve
Reject
Verify
Submit
Pay
```

harus:

```text
disabled while processing
```

Backend idempotency tetap mandatory.

---

# 21. 409 Conflict Handling

Jika backend mengembalikan:

```text
409 Conflict
```

Frontend:

```text
show warning
refresh claim
refresh timeline
refresh allowed actions
```

Contoh:

> Claim telah diproses oleh user lain. Data terbaru sedang dimuat.

---

# 22. 401 Handling

Jika:

```text
401
```

frontend:

```text
clear auth state
redirect login
```

---

# 23. 403 Handling

Jika:

```text
403
```

frontend tidak redirect login.

Tampilkan:

> Anda tidak memiliki izin untuk melakukan tindakan ini.

---

# 24. 422 Handling

Validation error dipetakan ke field:

```text
errors.claim_date
errors.receipts.0.amount
errors.bbm.distance_km
```

---

# 25. Responsive Strategy

Breakpoints:

```text
Mobile
Tablet
Desktop
Large Desktop
```

Mobile default.

---

# 26. Mobile Navigation

```text
Header
Content
Bottom Navigation
```

Primary action dapat menggunakan sticky bottom bar.

---

# 27. Desktop Navigation

```text
Sidebar
Header
Main Content
Optional Right Panel
```

---

# 28. Dashboard Component Tree

```text
DashboardPage
 ├── KpiGrid
 │    ├── ClaimCountCard
 │    ├── ClaimAmountCard
 │    ├── PendingCard
 │    ├── SlaCard
 │    └── PaymentCard
 │
 ├── ClaimTrendChart
 ├── ClaimTypeChart
 ├── SlaOverview
 └── RecentClaims
```

Data dashboard harus role-aware.

---

# 29. Reports

```text
ReportsPage
 ├── ReportSelector
 ├── FilterPanel
 ├── SummaryCards
 ├── ReportTable
 └── ExportButton
```

---

# 30. Filter State

Filter dapat disimpan pada URL:

```text
/reports/claims?type=BBM&status=PAID
```

Manfaat:

* shareable;
* browser navigation;
* refresh persistence.

---

# 31. Money Formatting

Frontend menampilkan:

```text
Rp 1.250.000
```

Gunakan centralized utility:

```text
formatRupiah()
```

Jangan melakukan financial calculation menggunakan JavaScript floating point sebagai authoritative calculation.

---

# 32. BBM UI

```text
BbmDetails
 ├── VehicleSelector
 ├── DistanceInput
 ├── FuelInput
 ├── ActualKmPerLiter
 ├── StandardKmPerLiter
 ├── Variance
 └── ComplianceBadge
```

Standard readonly.

---

# 33. Health UI

```text
HealthSummary
 ├── AnnualLimit
 ├── PaidUtilization
 ├── PendingExposure
 ├── Remaining
 └── UtilizationProgress
```

---

# 34. Accessibility

Minimum:

* keyboard navigation;
* visible focus;
* semantic HTML;
* labels;
* aria attributes when required;
* accessible error;
* sufficient contrast;
* no color-only meaning;
* 44px touch targets.

---

# 35. Notification UI

```text
NotificationBell
NotificationDropdown
NotificationPage
```

Unread count dari server.

---

# 36. Error Boundary

Global error handling untuk:

```text
500
network error
timeout
unexpected response
```

User mendapat message yang actionable.

Developer mendapat logging yang cukup.

---

# 37. Security Boundary

Frontend tidak boleh dipercaya untuk:

```text
authorization
claim total
workflow state
approval identity
payment eligibility
health limit
BBM standard
```

Semua authoritative value berasal dari backend.

---

# 38. Performance

Gunakan:

* lazy-loaded routes;
* pagination;
* debounced search;
* server-side filtering;
* component lazy loading;
* image compression;
* document preview optimization.

Jangan load seluruh claim history sekaligus.

---

# 39. State Synchronization

Setelah workflow action:

```text
execute action
      ↓
receive server response
      ↓
replace claim state
      ↓
refresh timeline
      ↓
refresh allowed actions
      ↓
refresh dashboard counters if necessary
```

---

# 40. Frontend Testing

Unit:

```text
composables
utils
formatters
permission helpers
```

Component:

```text
ClaimForm
ReceiptManager
DocumentUploader
WorkflowActionPanel
```

E2E:

```text
login
create claim
submit
review
verify
approve
payment
```

---

# 41. Frontend Definition of Done

* [ ] Component hierarchy.
* [ ] Pages.
* [ ] Composables.
* [ ] Stores.
* [ ] API client.
* [ ] Permission system.
* [ ] Workflow actions.
* [ ] Error handling.
* [ ] Loading state.
* [ ] 401/403/409/422 handling.
* [ ] Responsive.
* [ ] Accessibility.
* [ ] BBM UI.
* [ ] Health UI.
* [ ] Payment UI.
* [ ] Dashboard.
* [ ] Reports.
* [ ] E2E testing.

---

**End of FRONTEND_ARCHITECTURE.md**
