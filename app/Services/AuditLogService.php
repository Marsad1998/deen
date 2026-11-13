<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Keys containing sensitive credentials or tokens that must never be persisted in audit trails.
     */
    protected array $sensitiveKeys = [
        'password',
        'password_confirmation',
        'current_password',
        'secret',
        'token',
        'access_token',
        'refresh_token',
        'remember_token',
        'api_key',
        'credit_card',
        'card_number',
        'cvv',
        'auth_token',
    ];

    /**
     * Record an audit log entry with automated credential redaction.
     */
    public function log(
        string $action,
        string $entity,
        ?int $entityId = null,
        ?array $changes = null,
        ?int $adminId = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): AuditLog {
        $sanitizedChanges = $changes !== null ? $this->sanitizeChanges($changes) : null;

        return AuditLog::create([
            'admin_id'   => $adminId,
            'action'     => $action,
            'entity'     => $entity,
            'entity_id'  => $entityId,
            'changes'    => $sanitizedChanges,
            'ip_address' => $ipAddress ?? Request::ip(),
            'user_agent' => $userAgent ?? Request::userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Recursively redact sensitive keys from audit log payloads.
     */
    public function sanitizeChanges(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            $lowerKey = strtolower((string) $key);
            if (in_array($lowerKey, $this->sensitiveKeys, true) || str_contains($lowerKey, 'password')) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeChanges($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}

