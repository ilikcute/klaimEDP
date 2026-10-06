<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\BbmController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EdpOperasionalController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

// Public Auth routes
Route::post('/login', [AuthController::class, 'login']);

// Document view (handles auth internally via Sanctum token or query token)
Route::get('/documents/{document}/view', [DocumentController::class, 'view']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Profile
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Claims CRUD
    Route::apiResource('claims', ClaimController::class);

    // Claim Receipts
    Route::get('/claims/{claim}/receipts', [ReceiptController::class, 'index']);
    Route::post('/claims/{claim}/receipts', [ReceiptController::class, 'store']);
    Route::put('/claims/{claim}/receipts/{receipt}', [ReceiptController::class, 'update']);
    Route::delete('/claims/{claim}/receipts/{receipt}', [ReceiptController::class, 'destroy']);

    // Documents
    Route::get('/claims/{claim}/documents', [DocumentController::class, 'index']);
    Route::post('/claims/{claim}/documents', [DocumentController::class, 'store']);
    Route::get('/documents/{document}/download', [DocumentController::class, 'download']);
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);

    // Workflow Transitions
    Route::post('/claims/{claim}/submit', [WorkflowController::class, 'submit']);
    Route::post('/claims/{claim}/review', [WorkflowController::class, 'review']);
    Route::post('/claims/{claim}/supervisor-verification', [WorkflowController::class, 'supervisorVerification']);
    Route::post('/claims/{claim}/manager-verification', [WorkflowController::class, 'managerVerification']);
    Route::post('/claims/{claim}/finance-verification', [WorkflowController::class, 'financeVerification']);
    Route::post('/claims/{claim}/management-approval', [WorkflowController::class, 'managementApproval']);
    Route::post('/claims/{claim}/request-revision', [WorkflowController::class, 'requestRevision']);
    Route::post('/claims/{claim}/reject', [WorkflowController::class, 'reject']);
    Route::post('/claims/{claim}/cancel', [WorkflowController::class, 'cancel']);

    // Payment
    Route::get('/claims/{claim}/payment', [PaymentController::class, 'show']);
    Route::post('/claims/{claim}/payment', [PaymentController::class, 'process']);

    // Master Data (EDP Operasional & Kendaraan)
    Route::apiResource('master/edp-operasional', EdpOperasionalController::class);
    Route::apiResource('master/vehicles', VehicleController::class);

    // BBM
    Route::get('/bbm/standards', [BbmController::class, 'getStandards']);
    Route::post('/bbm/standards', [BbmController::class, 'storeStandard']);
    Route::get('/vehicles', [BbmController::class, 'getVehicles']);
    Route::get('/claims/{claim}/bbm', [BbmController::class, 'getClaimBbm']);
    Route::put('/claims/{claim}/bbm', [BbmController::class, 'updateClaimBbm']);

    // Health
    Route::get('/health/summary', [HealthController::class, 'summary']);

    // Reports
    Route::get('/reports/claims', [ReportController::class, 'claims']);
    Route::get('/reports/claims/export', [ReportController::class, 'export']);
    Route::get('/reports/by-type', [ReportController::class, 'byType']);
    Route::get('/reports/historical', [ReportController::class, 'historical']);
    Route::get('/reports/sla', [ReportController::class, 'sla']);
    Route::get('/reports/bbm', [ReportController::class, 'bbm']);
    Route::get('/reports/health', [ReportController::class, 'health']);
    Route::get('/reports/aging', [ReportController::class, 'aging']);
    Route::get('/reports/management-approval', [ReportController::class, 'managementApproval']);
    Route::get('/reports/duplicates', [ReportController::class, 'duplicates']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // Audit Events
    Route::get('/audit-events', [AuditController::class, 'index']);

    // User & Role Permission Management
    Route::get('/users/permissions', [UserController::class, 'permissionsList']);
    Route::get('/users/roles', [UserController::class, 'rolesList']);
    Route::put('/users/roles/{role}/permissions', [UserController::class, 'updateRolePermissions']);
    Route::apiResource('users', UserController::class);
});
