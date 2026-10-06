<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole(['ADMIN', 'EDP_MANAGER', 'DBM', 'BM'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $query = AuditEvent::with('actor')->latest('id');

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }
        if ($request->filled('actor_id')) {
            $query->where('actor_id', $request->actor_id);
        }

        $events = $query->paginate($request->per_page ?? 25);

        return response()->json([
            'success' => true,
            'data' => $events->items(),
            'pagination' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
            ],
        ]);
    }
}
