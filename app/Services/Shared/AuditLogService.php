<?php

namespace App\Services\Shared;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogService
{
    public function record(User $actor, Model $subject, string $event, array $metadata = []): void
    {
        $auditLog = new AuditLog;
        $auditLog->forceFill([
            'actor_user_id' => $actor->id,
            'event' => $event,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
        $auditLog->subject()->associate($subject);
        $auditLog->save();
    }
}
