<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryHistory extends Model
{
    protected $table = 'inventory_histories';

    protected $fillable = [
        'product_variant_id',
        'change_qty',
        'previous_qty',
        'new_qty',
        'reason',
        'admin_id',
    ];

    protected function casts(): array
    {
        return [
            'change_qty' => 'integer',
            'previous_qty' => 'integer',
            'new_qty' => 'integer',
        ];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
