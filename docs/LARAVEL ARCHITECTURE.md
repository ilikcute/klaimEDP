# LARAVEL ARCHITECTURE

## Sistem Rekap, Tracking & Analisa Klaim / Reimbursement EDP

**Version:** 1.0

---

# 1. Architecture

Recommended architecture:

```text
Vue.js
   │
   │ HTTP/API
   ▼
Laravel
   │
   ├── Controllers
   ├── Requests
   ├── Policies
   ├── Actions
   ├── Domain Services
   ├── Events
   ├── Listeners
   ├── Jobs
   ├── Notifications
   └── Queries
   │
   ▼
MySQL
```

---

# 2. Architectural Principle

Controller harus tipis.

Buruk:

```text
Controller
 ├── validation
 ├── authorization
 ├── calculation
 ├── workflow
 ├── database transaction
 ├── notification
 └── reporting
```

Lebih baik:

```text
Controller
   ↓
Form Request
   ↓
Policy
   ↓
Action / Service
   ↓
Domain Service
   ↓
Model / Query
```

---

# 3. Recommended Folder Structure

```text
app/
├── Actions/
│   ├── Claims/
│   │   ├── CreateClaimAction.php
│   │   ├── UpdateClaimAction.php
│   │   ├── SubmitClaimAction.php
│   │   ├── RequestRevisionAction.php
│   │   └── RejectClaimAction.php
│   │
│   ├── Workflow/
│   │   ├── TransitionClaimAction.php
│   │   ├── VerifyClaimAction.php
│   │   └── ApproveClaimAction.php
│   │
│   └── Payments/
│       └── ProcessPaymentAction.php
│
├── Domain/
│   ├── Claims/
│   │   ├── Services/
│   │   ├── Rules/
│   │   ├── DTOs/
│   │   └── Enums/
│   │
│   ├── Workflow/
│   │   ├── StateMachine/
│   │   ├── Guards/
│   │   └── Transitions/
│   │
│   ├── BBM/
│   ├── Health/
│   └── SLA/
│
├── Events/
├── Listeners/
├── Jobs/
├── Notifications/
├── Policies/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Models/
├── Queries/
├── Repositories/
└── Support/
```

---

# 4. Models

Core Eloquent models:

```text
User
Role
Permission

Claim
ClaimReceipt
ClaimDocument

Vehicle
BbmStandard
ClaimBbmDetail

HealthPolicy
ClaimHealthDetail

WorkflowInstance
WorkflowAction
ClaimStatusHistory

SlaPolicy
SlaInstance

Payment

AuditEvent
Notification
OutboxEvent
```

---

# 5. Enums

Use PHP backed enums:

```text
ClaimType
ClaimState
WorkflowAction
PaymentStatus
DocumentStatus
DocumentType
SlaStatus
VehicleType
```

Contoh:

```php
enum ClaimState: string
{
    case DRAFT = 'DRAFT';
    case SUBMITTED = 'SUBMITTED';
    case EDP_REVIEW = 'EDP_REVIEW';
    case SUPERVISOR_VERIFICATION = 'SUPERVISOR_VERIFICATION';
    case MANAGER_VERIFICATION = 'MANAGER_VERIFICATION';
    case FINANCE_VERIFICATION = 'FINANCE_VERIFICATION';
    case MANAGEMENT_APPROVAL = 'MANAGEMENT_APPROVAL';
    case APPROVED = 'APPROVED';
    case PAYMENT_PROCESS = 'PAYMENT_PROCESS';
    case PAID = 'PAID';
    case NEED_REVISION = 'NEED_REVISION';
    case REJECTED = 'REJECTED';
    case CANCELLED = 'CANCELLED';
}
```

---

# 6. Form Requests

Contoh:

```text
CreateClaimRequest
UpdateClaimRequest
AddReceiptRequest
UploadClaimDocumentRequest

SubmitClaimRequest

ReviewClaimRequest
VerifyClaimRequest
ApprovalRequest
RevisionRequest
RejectClaimRequest

PaymentRequest
```

Validation dilakukan sebelum Action.

---

# 7. Policies

Policies:

```text
ClaimPolicy
ClaimDocumentPolicy
PaymentPolicy
WorkflowPolicy
ReportPolicy
AuditEventPolicy
```

Contoh:

```text
ClaimPolicy@view
ClaimPolicy@update
ClaimPolicy@submit
ClaimPolicy@review
ClaimPolicy@verify
ClaimPolicy@approve
ClaimPolicy@pay
```

---

# 8. Gates vs Policies

Gunakan:

```text
Policy
```

untuk authorization terhadap resource.

Gunakan domain guard untuk:

```text
Apakah state memungkinkan action?
```

Jadi:

```text
Policy
+
Workflow Guard
```

bukan salah satu saja.

---

# 9. Claim Actions

### CreateClaimAction

Responsibilities:

* generate claim number;
* set requester;
* set DRAFT;
* create claim;
* initialize workflow;
* audit.

---

### SubmitClaimAction

Responsibilities:

* lock claim;
* validate state;
* validate receipts;
* validate documents;
* validate domain rules;
* transition;
* history;
* audit;
* event.

---

# 10. Workflow Service

Core service:

```text
ClaimWorkflowService
```

API conceptual:

```php
transition(
    Claim $claim,
    WorkflowAction $action,
    User $actor,
    array $context = []
)
```

Service:

```text
load current state
↓
authorize
↓
guard
↓
lock
↓
transition
↓
persist history
↓
persist audit
↓
dispatch event
```

---

# 11. State Machine Implementation

Recommended:

```text
ClaimStateMachine
```

Responsibilities:

```text
canTransition()
getAllowedActions()
resolveNextState()
validateGuard()
```

Do not duplicate transition mapping across controllers.

---

# 12. Domain Rules

Rules:

```text
ClaimMustHaveReceiptRule
ClaimTotalIntegrityRule
ClaimDocumentCompletenessRule

BbmCalculationRule
BbmStandardRule

HealthAnnualLimitRule

ManagementApprovalRule
PaymentEligibilityRule
```

---

# 13. BBM Service

```text
BbmCalculationService
```

Input:

```text
distance_km
fuel_liter
vehicle_type
claim_date
```

Output:

```text
actual_km_per_liter
standard_km_per_liter
variance
compliance_status
```

Formula:

```text
distance_km / fuel_liter
```

---

# 14. Health Service

```text
HealthUtilizationService
```

Methods:

```text
getAnnualLimit()
getPaidUtilization()
getPendingExposure()
getRemainingLimit()
```

Critical calculation should use transaction/locking where concurrent claims can affect policy enforcement.

---

# 15. Payment Service

```text
PaymentService
```

Responsibilities:

* eligibility;
* amount validation;
* idempotency;
* locking;
* payment persistence;
* state transition;
* audit;
* event.

---

# 16. Repository Strategy

Do not create repositories mechanically for every model.

Use repositories/query objects where query complexity justifies abstraction.

Recommended:

```text
ClaimQuery
ClaimReportQuery
DashboardQuery
SlaReportQuery
BbmReportQuery
HealthReportQuery
```

Eloquent is sufficient for simple CRUD.

---

# 17. Query Objects

Example:

```text
ClaimListQuery
```

handles:

* filtering;
* searching;
* sorting;
* eager loading;
* pagination.

Reporting uses dedicated queries.

---

# 18. N+1 Prevention

Use:

```text
with()
withCount()
withSum()
withExists()
```

and SQL aggregation.

Never:

```text
foreach claim:
    claim.receipts()->count()
```

untuk large lists.

---

# 19. Events

Domain events:

```text
ClaimCreated
ClaimSubmitted
ClaimNeedsRevision
ClaimVerified
ClaimApproved
ClaimRejected
PaymentStarted
PaymentCompleted
ClaimSlaAtRisk
ClaimSlaBreached
```

---

# 20. Listeners

Examples:

```text
SendClaimSubmittedNotification
SendRevisionNotification
SendApprovalNotification
SendPaymentNotification

CreateAuditRecord
CreateOutboxEvent
```

Audit critical events harus tetap reliable.

---

# 21. Jobs

Queue jobs:

```text
SendTelegramNotificationJob
GenerateClaimReportJob
GenerateExcelExportJob
ProcessOutboxEventJob
CalculateHistoricalAnalyticsJob
```

Heavy operation jangan dilakukan synchronous di HTTP request.

---

# 22. Notifications

Notification channels:

```text
database
telegram
```

Email dapat ditambahkan future.

Notification harus asynchronous.

---

# 23. Outbox

Business transaction:

```text
DB transaction
    ↓
outbox_events
    ↓
COMMIT
```

Worker:

```text
outbox_events
    ↓
Telegram
```

Jika Telegram gagal:

```text
retry
```

bukan rollback claim.

---

# 24. Service Transaction Boundary

Business Action yang mengubah state harus memiliki transaction boundary.

Contoh:

```text
DB::transaction(
    fn () => submitClaim(...)
)
```

Jangan membuka transaction di banyak layer secara tidak terkendali.

---

# 25. Locking

Critical lock:

```text
Claim::query()
    ->whereKey($claim->id)
    ->lockForUpdate()
    ->first();
```

Lock dilakukan setelah transaction dimulai.

---

# 26. Authorization Flow

```text
Request
  ↓
Authentication
  ↓
Policy
  ↓
Action
  ↓
Domain Guard
  ↓
Transaction
  ↓
State Machine
```

---

# 27. API Controllers

Recommended:

```text
ClaimController
ClaimReceiptController
ClaimDocumentController
ClaimWorkflowController
ClaimPaymentController

DashboardController
ReportController
BbmController
HealthController
```

---

# 28. Service Responsibilities

Controller:

```text
HTTP orchestration
```

Action:

```text
business use case
```

Domain service:

```text
complex business logic
```

Model:

```text
persistence + relationships
```

Query:

```text
read optimization
```

Policy:

```text
authorization
```

Job:

```text
asynchronous work
```

Event:

```text
business fact
```

---

# 29. Exception Strategy

Custom exceptions:

```text
InvalidWorkflowTransitionException
ClaimNotEditableException
PaymentNotAllowedException
DuplicateIdempotencyKeyException
BusinessRuleViolationException
```

Map to appropriate HTTP semantics.

---

# 30. API Resources

Use Laravel API Resources:

```text
ClaimResource
ClaimDetailResource
ClaimReceiptResource
ClaimDocumentResource
WorkflowTimelineResource
PaymentResource
DashboardResource
```

Do not expose raw Eloquent models blindly.

---

# 31. Configuration

Business configuration should be separated:

```text
config/reimbursement.php
```

But dynamic policy such as:

* BBM standard;
* Health limit;
* SLA;
* approval policy;

belongs in database if management must change it without deployment.

---

# 32. Logging

Application logs:

```text
INFO
WARNING
ERROR
```

Do not log:

* password;
* token;
* sensitive document contents;
* unnecessary personal data.

---

# 33. Testing Architecture

Tests:

```text
Feature/
    Claims/
    Workflow/
    Payment/
    Authorization/
    Documents/
    Reports/

Unit/
    Domain/
    Rules/
    StateMachine/
    Services/
```

Critical workflow should have exhaustive transition tests.

---

# 34. Architecture Definition of Done

* [ ] Thin controllers.
* [ ] Form Requests.
* [ ] Policies.
* [ ] Actions.
* [ ] Domain services.
* [ ] State machine.
* [ ] Query objects.
* [ ] Events.
* [ ] Jobs.
* [ ] Notifications.
* [ ] Outbox.
* [ ] Transactions.
* [ ] Row locking.
* [ ] API resources.
* [ ] Enums.
* [ ] Feature tests.
* [ ] Unit tests.

---

**End of LARAVEL_ARCHITECTURE.md**
