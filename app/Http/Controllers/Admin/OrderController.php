<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Services\AuditLogService;
use App\Services\InventoryAdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display a listing of orders with real-time filtering, search, and status counters.
     */
    public function index(Request $request): View
    {
        $query = Order::query()->withCount('items')->latest();

        // Keyword Search across Order Number, Customer Name, Phone, and City
        if ($search = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'LIKE', "%{$search}%")
                  ->orWhere('customer_name', 'LIKE', "%{$search}%")
                  ->orWhere('customer_phone', 'LIKE', "%{$search}%")
                  ->orWhere('customer_email', 'LIKE', "%{$search}%")
                  ->orWhere('shipping_city', 'LIKE', "%{$search}%");
            });
        }

        // Status Filter
        if ($status = $request->input('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        // Payment Method Filter
        if ($paymentMethod = $request->input('payment_method')) {
            if ($paymentMethod !== 'all') {
                $query->where('payment_method', $paymentMethod);
            }
        }

        // Payment Status Filter
        if ($paymentStatus = $request->input('payment_status')) {
            if ($paymentStatus !== 'all') {
                $query->where('payment_status', $paymentStatus);
            }
        }

        // Date Range Filtering
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $orders = $query->paginate(15)->withQueryString();

        // Calculate Status Counters for Quick Filter Tabs
        $counts = [
            'all'        => Order::count(),
            'pending'    => Order::where('status', 'pending')->count(),
            'confirmed'  => Order::where('status', 'confirmed')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped'    => Order::where('status', 'shipped')->count(),
            'delivered'  => Order::where('status', 'delivered')->count(),
            'cancelled'  => Order::where('status', 'cancelled')->count(),
        ];

        return view('admin.orders.index', [
            'orders'  => $orders,
            'counts'  => $counts,
            'filters' => $request->only(['q', 'status', 'payment_method', 'payment_status', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Display comprehensive order details, customer contact links, and fulfillment status.
     */
    public function show(Order $order): View
    {
        $order->load(['items.product', 'items.variant.product']);

        $statusHistories = DB::table('order_status_history')
            ->where('order_id', $order->id)
            ->leftJoin('admins', 'admins.id', '=', 'order_status_history.changed_by_admin_id')
            ->select('order_status_history.*', 'admins.username as admin_username')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.orders.show', [
            'order'           => $order,
            'statusHistories' => $statusHistories,
        ]);
    }

    /**
     * Update an order's fulfillment and payment status with automated inventory deduction on dispatch.
     */
    public function updateStatus(
        Request $request,
        Order $order,
        AuditLogService $auditLogService,
        InventoryAdjustmentService $inventoryService
    ): RedirectResponse {
        $validated = $request->validate([
            'status'         => ['required', 'string', 'in:pending,confirmed,processing,shipped,delivered,cancelled'],
            'payment_status' => ['required', 'string', 'in:pending,paid,refunded,failed'],
            'notes'          => ['nullable', 'string', 'max:3000'],
        ]);

        $oldStatus = $order->status;
        $newStatus = $validated['status'];
        $oldPaymentStatus = $order->payment_status;
        $admin = Auth::guard('admin')->user();
        $adminId = $admin?->id;

        // Eager load items and associated variants
        $order->load(['items.variant.product']);

        // Check dispatch transition: moving to shipped/delivered and inventory has not yet been deducted
        $isDispatchTransition = in_array($newStatus, ['shipped', 'delivered']) && $order->inventory_deducted_at === null;

        // Check cancellation transition: moving to cancelled after inventory was already deducted (restore stock)
        $isCancelTransition = ($newStatus === 'cancelled') && $order->inventory_deducted_at !== null;

        try {
            DB::transaction(function () use (
                $order,
                $validated,
                $oldStatus,
                $newStatus,
                $oldPaymentStatus,
                $adminId,
                $isDispatchTransition,
                $isCancelTransition,
                $inventoryService,
                $auditLogService
            ) {
                // If transitioning to dispatched, check stock availability then deduct atomically
                if ($isDispatchTransition) {
                    // First pass: verify sufficient stock across all items before any deduction
                    foreach ($order->items as $item) {
                        $variant = $item->resolveVariant();
                        if ($variant) {
                            $variant->refresh();
                            if ($variant->stock_quantity < $item->quantity) {
                                throw new \RuntimeException(
                                    "Cannot dispatch order #{$order->order_number}: Insufficient inventory for '{$item->product_name}' (SKU: {$variant->sku}). Available stock is {$variant->stock_quantity}, but order requires {$item->quantity}."
                                );
                            }
                        }
                    }

                    // Second pass: deduct stock through InventoryAdjustmentService
                    foreach ($order->items as $item) {
                        $variant = $item->resolveVariant();
                        if ($variant) {
                            // Link product variant if missing on older order record
                            if (! $item->product_variant_id) {
                                $item->update([
                                    'product_variant_id' => $variant->id,
                                    'product_id'         => $variant->product_id,
                                ]);
                            }

                            $reason = "Order #{$order->order_number} dispatched to {$order->shipping_city}";
                            $inventoryService->adjustStock(
                                variantId: $variant->id,
                                changeQty: -$item->quantity,
                                reason: $reason,
                                adminId: $adminId
                            );
                        }
                    }

                    $order->dispatched_at = now();
                    $order->inventory_deducted_at = now();
                }

                // If an order that had inventory deducted is cancelled, restore the inventory
                if ($isCancelTransition) {
                    foreach ($order->items as $item) {
                        $variant = $item->resolveVariant();
                        if ($variant) {
                            $reason = "Order #{$order->order_number} cancelled — stock restored";
                            $inventoryService->adjustStock(
                                variantId: $variant->id,
                                changeQty: $item->quantity,
                                reason: $reason,
                                adminId: $adminId
                            );
                        }
                    }

                    $order->inventory_deducted_at = null;
                }

                // Update order attributes
                $order->status = $newStatus;
                $order->payment_status = $validated['payment_status'];
                if (array_key_exists('notes', $validated) && $validated['notes'] !== null) {
                    $order->notes = $validated['notes'];
                }
                $order->save();

                // Record into order_status_history
                DB::table('order_status_history')->insert([
                    'order_id'            => $order->id,
                    'status'              => $order->status,
                    'comment'             => $validated['notes'] ?? "Status updated from {$oldStatus} to {$order->status}",
                    'changed_by_admin_id' => $adminId,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);

                // Audit Trail
                $auditLogService->log(
                    action: 'order.status_updated',
                    entity: 'Order',
                    entityId: $order->id,
                    changes: [
                        'order_number'           => $order->order_number,
                        'previous_status'        => $oldStatus,
                        'new_status'             => $order->status,
                        'previous_payment_status' => $oldPaymentStatus,
                        'new_payment_status'     => $order->payment_status,
                        'inventory_deducted'     => $order->inventory_deducted_at !== null,
                        'dispatched_at'          => $order->dispatched_at?->toIso8601String(),
                        'notes_updated'          => !empty($validated['notes']),
                    ]
                );
            });
        } catch (\Throwable $e) {
            return redirect()->route('admin.orders.show', $order->id)
                ->withErrors(['inventory' => $e->getMessage()])
                ->withInput();
        }

        $message = "Order #{$order->order_number} status updated to " . ucfirst($order->status) . " successfully.";
        if ($isDispatchTransition) {
            $message .= " Stock was automatically deducted from inventory.";
        } elseif ($isCancelTransition) {
            $message .= " Stock was automatically restored to inventory.";
        }

        return redirect()->route('admin.orders.show', $order->id)
            ->with('success', $message);
    }

    /**
     * Display a clean, printer-friendly invoice/receipt for customer packing or showroom record.
     */
    public function printInvoice(Order $order): View
    {
        $order->load(['items.product', 'items.variant']);

        return view('admin.orders.invoice', [
            'order' => $order,
        ]);
    }
}
