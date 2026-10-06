<?php

namespace App\Http\Controllers;

use App\Enums\DocumentStatus;
use App\Enums\DocumentType;
use App\Models\Claim;
use App\Models\ClaimDocument;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function __construct(protected AuditService $auditService) {}

    public function index(Claim $claim): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $claim->documents()->with(['receipt', 'uploader'])->get(),
        ]);
    }

    public function store(Request $request, Claim $claim): JsonResponse
    {
        $user = $request->user();

        if (!$claim->current_state->isEditable() && !$user->hasRole(['ADMIN', 'FINANCE', 'EDP_ADM'])) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak dapat diunggah pada status klaim saat ini.',
            ], 422);
        }

        $validated = $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,pdf,webp|max:10240', // max 10MB
            'document_type' => 'required|string',
            'claim_receipt_id' => 'nullable|exists:claim_receipts,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'gps_accuracy_meters' => 'nullable|numeric',
            'captured_at' => 'nullable|date',
            'location_address' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $sha256 = hash_file('sha256', $file->getRealPath());

        $path = $file->store("claims/{$claim->id}", 'local');

        $docType = DocumentType::tryFrom($validated['document_type']) ?? DocumentType::SUPPORTING_DOC;

        $doc = $claim->documents()->create([
            'claim_receipt_id' => $validated['claim_receipt_id'] ?? null,
            'document_type' => $docType,
            'status' => DocumentStatus::RECEIVED,
            'original_filename' => $file->getClientOriginalName(),
            'storage_disk' => 'local',
            'storage_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'checksum_sha256' => $sha256,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'gps_accuracy_meters' => $validated['gps_accuracy_meters'] ?? null,
            'captured_at' => $validated['captured_at'] ?? now(),
            'location_address' => $validated['location_address'] ?? null,
            'uploaded_by' => $user->id,
            'received_at' => now(),
        ]);

        $this->auditService->log($user, 'DOCUMENT_UPLOADED', ClaimDocument::class, $doc->id, null, [
            'filename' => $doc->original_filename,
            'size' => $doc->file_size,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil diunggah.',
            'data' => $doc->load('uploader'),
        ], 201);
    }

    public function download(Request $request, ClaimDocument $document): StreamedResponse|JsonResponse
    {
        $user = $request->user();
        $claim = $document->claim;

        if (!$user->hasRole('ADMIN') && $user->hasRole('EDP_OPERASIONAL') && $claim->requester_id !== $user->id) {
            if (!$user->hasRole(['EDP_ADM', 'EDP_SUPERVISOR', 'EDP_MANAGER', 'FINANCE', 'DBM', 'BM'])) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }
        }

        if (!Storage::disk($document->storage_disk)->exists($document->storage_path)) {
            return response()->json(['success' => false, 'message' => 'File tidak ditemukan di storage.'], 404);
        }

        return Storage::disk($document->storage_disk)->download(
            $document->storage_path,
            $document->original_filename,
            ['Content-Type' => $document->mime_type]
        );
    }

    public function view(Request $request, ClaimDocument $document)
    {
        $user = $request->user();
        if (!$user && $request->filled('token')) {
            $tokenObj = \Laravel\Sanctum\PersonalAccessToken::findToken($request->token);
            if ($tokenObj) {
                $user = $tokenObj->tokenable;
            }
        }
        if (!$user && $request->bearerToken()) {
            $tokenObj = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
            if ($tokenObj) {
                $user = $tokenObj->tokenable;
            }
        }

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $claim = $document->claim;
        if (!$user->hasRole('ADMIN') && $user->hasRole('EDP_OPERASIONAL') && $claim->requester_id !== $user->id) {
            if (!$user->hasRole(['EDP_ADM', 'EDP_SUPERVISOR', 'EDP_MANAGER', 'FINANCE', 'DBM', 'BM'])) {
                return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
            }
        }

        if (!Storage::disk($document->storage_disk)->exists($document->storage_path)) {
            return response()->json(['success' => false, 'message' => 'File tidak ditemukan di storage.'], 404);
        }

        return Storage::disk($document->storage_disk)->response(
            $document->storage_path,
            $document->original_filename,
            [
                'Content-Type' => $document->mime_type,
                'Content-Disposition' => 'inline; filename="' . $document->original_filename . '"'
            ]
        );
    }

    public function destroy(Request $request, ClaimDocument $document): JsonResponse
    {
        $user = $request->user();
        $claim = $document->claim;

        if (!$claim->current_state->isEditable() && !$user->hasRole('ADMIN')) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen tidak dapat dihapus pada status klaim saat ini.',
            ], 422);
        }

        if ($document->uploaded_by !== $user->id && !$user->hasRole('ADMIN')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        if (Storage::disk($document->storage_disk)->exists($document->storage_path)) {
            Storage::disk($document->storage_disk)->delete($document->storage_path);
        }

        $document->delete();
        $this->auditService->log($user, 'DOCUMENT_DELETED', ClaimDocument::class, $document->id, $document->toArray(), null);

        return response()->json([
            'success' => true,
            'message' => 'Dokumen berhasil dihapus.',
        ]);
    }
}
