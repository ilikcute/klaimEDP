<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Services\AuditService;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected AuditService $auditService
    ) {}

    public function claims(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->reportService->getClaimSummaryReport($request->all()),
        ]);
    }

    public function byType(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->reportService->getClaimByTypeReport(),
        ]);
    }

    public function historical(Request $request): JsonResponse
    {
        $months = (int)($request->months ?? 6);
        return response()->json([
            'success' => true,
            'data' => $this->reportService->getHistoricalTrendReport($months),
        ]);
    }

    public function sla(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->reportService->getSlaReport(),
        ]);
    }

    public function bbm(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->reportService->getBbmReport(),
        ]);
    }

    public function health(Request $request): JsonResponse
    {
        $year = $request->filled('year') ? (int)$request->year : null;
        return response()->json([
            'success' => true,
            'data' => $this->reportService->getHealthReport($year),
        ]);
    }

    public function aging(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->reportService->getAgingReport(),
        ]);
    }

    public function managementApproval(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->reportService->getManagementApprovalReport(),
        ]);
    }

    public function duplicates(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->reportService->getDuplicateDetectionReport(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();
        $this->auditService->log($user, 'REPORT_EXPORTED', Claim::class, 0, null, [
            'format' => 'CSV',
            'filters' => $request->all(),
        ]);

        $claims = Claim::with(['requester', 'payment'])->latest('claim_date')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="rekap_klaim_' . date('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () use ($claims) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Nomor Klaim',
                'Requester',
                'Jenis Klaim',
                'Tanggal Klaim',
                'Total Nominal (IDR)',
                'Status Saat Ini',
                'Tanggal Diajukan',
                'Tanggal Disetujui',
                'Tanggal Dibayar',
                'Keterangan',
            ]);

            foreach ($claims as $claim) {
                fputcsv($handle, [
                    $claim->claim_number,
                    $claim->requester?->name,
                    $claim->claim_type->value,
                    $claim->claim_date?->format('Y-m-d'),
                    $claim->total_amount,
                    $claim->current_state->value,
                    $claim->submitted_at?->format('Y-m-d H:i:s'),
                    $claim->approved_at?->format('Y-m-d H:i:s'),
                    $claim->paid_at?->format('Y-m-d H:i:s'),
                    $claim->description,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
