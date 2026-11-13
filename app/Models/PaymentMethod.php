<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $table = 'payment_methods';

    protected $fillable = [
        'code',
        'name',
        'subtitle',
        'is_enabled',
        'requires_proof',
        'account_title',
        'account_number',
        'bank_name',
        'branch_name',
        'iban',
        'instructions',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled'     => 'boolean',
            'requires_proof' => 'boolean',
            'display_order'  => 'integer',
        ];
    }

    /**
     * Scope for active/enabled methods in storefront display order.
     */
    public function scopeActive($query)
    {
        return $query->where('is_enabled', true)->orderBy('display_order');
    }
}
