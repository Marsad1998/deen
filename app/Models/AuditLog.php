<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'audit_logs';

    protected $fillable = [
        'admin_id',
        'action',
        'entity',
        'entity_id',
        'changes',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Prevent updates to existing audit logs.
     */
    public function save(array $options = []): bool
    {
        if ($this->exists) {
            throw new RuntimeException('Audit logs are strictly append-only and cannot be modified.');
        }

        if (! $this->created_at) {
            $this->created_at = now();
        }

        return parent::save($options);
    }

    /**
     * Prevent deleting audit logs.
     */
    public function delete(): ?bool
    {
        throw new RuntimeException('Audit logs are strictly append-only and cannot be deleted.');
    }
}
