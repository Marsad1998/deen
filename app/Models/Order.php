<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'order_number',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_recipient_name',
        'shipping_recipient_phone',
        'shipping_address_line1',
        'shipping_address_line2',
        'shipping_city',
        'shipping_province',
        'shipping_postal_code',
        'shipping_country',
        'delivery_instructions',
        'shipping_address_id',
        'status',
        'payment_method',
        'payment_status',
        'payment_proof_path',
        'transaction_reference',
        'currency',
        'subtotal',
        'discount_amount',
        'coupon_id',
        'coupon_code',
        'shipping_cost',
        'shipping_method_id',
        'tax_amount',
        'total_amount',
        'notes',
        'dispatched_at',
        'inventory_deducted_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'              => 'decimal:2',
            'discount_amount'       => 'decimal:2',
            'shipping_cost'         => 'decimal:2',
            'tax_amount'            => 'decimal:2',
            'total_amount'          => 'decimal:2',
            'dispatched_at'         => 'datetime',
            'inventory_deducted_at' => 'datetime',
            'archived_at'           => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'COD', 'cod'      => 'Cash on Delivery (COD)',
            'bank_transfer'   => 'Direct Bank Transfer',
            'jazzcash'        => 'JazzCash Mobile Account',
            'easypaisa'       => 'Easypaisa Mobile Account',
            'payfast'         => 'PayFast Debit/Credit Card',
            'showroom_pickup' => 'Showroom Counter Pickup (Cash/Card)',
            default           => strtoupper($this->payment_method),
        };
    }

    public function getPaymentProofUrlAttribute(): ?string
    {
        if (! $this->payment_proof_path) {
            return null;
        }

        if (str_starts_with($this->payment_proof_path, 'http://') || str_starts_with($this->payment_proof_path, 'https://')) {
            return $this->payment_proof_path;
        }

        return asset('storage/' . ltrim($this->payment_proof_path, '/'));
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'Order Placed (Pending Verification)',
            'confirmed'  => 'Confirmed by Showroom',
            'processing' => 'Processing & Packaging',
            'shipped'    => 'Dispatched with Courier',
            'delivered'  => 'Delivered',
            'cancelled'  => 'Cancelled',
            default      => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'bg-amber-50 text-amber-800 border-amber-200',
            'confirmed'  => 'bg-blue-50 text-blue-800 border-blue-200',
            'processing' => 'bg-purple-50 text-purple-800 border-purple-200',
            'shipped'    => 'bg-indigo-50 text-indigo-800 border-indigo-200',
            'delivered'  => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'cancelled'  => 'bg-rose-50 text-rose-800 border-rose-200',
            default      => 'bg-slate-50 text-slate-800 border-slate-200',
        };
    }

    public function getPaymentStatusBadgeClassAttribute(): string
    {
        return match ($this->payment_status) {
            'paid'     => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'pending'  => 'bg-amber-50 text-amber-800 border-amber-200',
            'refunded' => 'bg-slate-100 text-slate-800 border-slate-200',
            'failed'   => 'bg-rose-50 text-rose-800 border-rose-200',
            default    => 'bg-slate-50 text-slate-700 border-slate-200',
        };
    }

    public function getCustomerWhatsappUrlAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', (string) ($this->customer_phone ?: $this->shipping_recipient_phone));
        if (str_starts_with($phone, '0')) {
            $phone = '92' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '92') && strlen($phone) === 10) {
            $phone = '92' . $phone;
        }

        $message = urlencode("Assalam o Alaikum {$this->customer_name}, this is DEEN Electronics Lahore regarding your order #{$this->order_number} (Total: Rs " . number_format($this->total_amount, 0) . "). Status: " . ucfirst($this->status) . ".");

        return "https://wa.me/{$phone}?text={$message}";
    }
}
