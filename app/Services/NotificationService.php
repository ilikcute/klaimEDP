<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Role;
use App\Models\User;

class NotificationService
{
    public function notify(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $refType = null,
        ?int $refId = null
    ): Notification {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'reference_type' => $refType,
            'reference_id' => $refId,
        ]);
    }

    public function notifyRole(
        string $roleName,
        string $type,
        string $title,
        string $message,
        ?string $refType = null,
        ?int $refId = null
    ): void {
        $role = Role::where('name', $roleName)->first();
        if (!$role) return;

        $users = $role->users()->where('is_active', true)->get();
        foreach ($users as $user) {
            $this->notify($user, $type, $title, $message, $refType, $refId);
        }
    }
}
