<?php

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public function log(
        ?User $actor,
        string $eventType,
        string $entityType,
        int $entityId,
        ?array $before = null,
        ?array $after = null
    ): AuditEvent {
        return AuditEvent::create([
            'actor_id' => $actor?->id,
            'event_type' => $eventType,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'before_data' => $before,
            'after_data' => $after,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }
}
