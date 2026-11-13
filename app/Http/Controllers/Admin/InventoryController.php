<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddInventoryRequest;
use App\Http\Requests\Admin\AdjustInventoryRequest;
use App\Models\InventoryHistory;
use App\Models\ProductVariant;
use App\Services\AuditLogService;
use App\Services\InventoryAdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryAdjustmentService $inventoryService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Display inventory stock list across variants with historical tracking.
     */
    public function index(Request $request): View
    {
        $query = ProductVariant::with(['product', 'inventoryHistories' => function ($q) {
            $q->latest()->limit(5);
        }]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhereHas('product', fn ($pq) => $pq->where('name', 'LIKE', "%{$search}%"));
            });
        }

        if ($filter = $request->input('filter')) {
            if ($filter === 'low_stock') {
                $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10);
            } elseif ($filter === 'out_of_stock') {
                $query->where('stock_quantity', '<=', 0);
            }
        }

        $variants = $query->orderBy('stock_quantity', 'asc')->paginate(20)->withQueryString();

        $recentHistories = InventoryHistory::with(['variant.product', 'admin'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('admin.inventory.index', compact('variants', 'recentHistories'));
    }

    /**
     * Show form to receive new inward warehouse inventory.
     */
    public function create(Request $request): View
    {
        $variants = ProductVariant::with('product')
            ->where('is_active', true)
            ->whereNull('archived_at')
            ->orderBy('sku')
            ->get();

        $preselectedVariantId = (int) $request->input('variant_id');

        return view('admin.inventory.create', compact('variants', 'preselectedVariantId'));
    }

    /**
     * Store inward stock inventory with audit and movement logging.
     */
    public function store(AddInventoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $admin = Auth::guard('admin')->user();
        $variant = ProductVariant::with('product')->findOrFail($validated['product_variant_id']);

        // Build a clear, human-readable reason
        $reasonParts = [$validated['inward_type']];
        if (! empty($validated['reference_number'])) {
            $reasonParts[] = 'Ref: ' . $validated['reference_number'];
        }
        if (! empty($validated['supplier_name'])) {
            $reasonParts[] = 'from ' . $validated['supplier_name'];
        }
        $reason = implode(' ', $reasonParts);
        if (! empty($validated['notes'])) {
            $reason .= ' — ' . $validated['notes'];
        }
        $reason = Str::limit($reason, 100, '');

        try {
            $result = $this->inventoryService->adjustStock(
                variantId: $variant->id,
                changeQty: (int) $validated['quantity'],
                reason: $reason,
                adminId: $admin?->id
            );

            $this->auditLogService->log(
                action: 'inventory.received',
                entity: 'ProductVariant',
                entityId: $variant->id,
                changes: [
                    'product_name'     => $variant->product?->name,
                    'sku'              => $variant->sku,
                    'quantity_added'   => (int) $validated['quantity'],
                    'previous_qty'     => $result['previous_qty'],
                    'new_qty'          => $result['new_qty'],
                    'inward_type'      => $validated['inward_type'],
                    'reference_number' => $validated['reference_number'] ?? null,
                    'supplier_name'    => $validated['supplier_name'] ?? null,
                    'reason'           => $reason,
                ],
                adminId: $admin?->id
            );

            $productTitle = $variant->product?->name ?? 'Product';
            return redirect()
                ->route('admin.inventory.index')
                ->with('status', "Successfully added +{$validated['quantity']} units to {$productTitle} ({$variant->sku}). Stock increased: {$result['previous_qty']} → {$result['new_qty']}.");
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['inventory' => $e->getMessage()]);
        }
    }

    /**
     * Adjust stock quantity for a variant and record historical movement.
     */
    public function adjust(AdjustInventoryRequest $request, ProductVariant $variant): RedirectResponse
    {
        $validated = $request->validated();
        $admin = Auth::guard('admin')->user();

        try {
            $result = $this->inventoryService->adjustStock(
                variantId: $variant->id,
                changeQty: (int) $validated['change_qty'],
                reason: $validated['reason'],
                adminId: $admin?->id
            );

            $this->auditLogService->log(
                action: 'inventory.adjusted',
                entity: 'ProductVariant',
                entityId: $variant->id,
                changes: [
                    'sku'          => $variant->sku,
                    'change_qty'   => $result['change_qty'],
                    'previous_qty' => $result['previous_qty'],
                    'new_qty'      => $result['new_qty'],
                    'reason'       => $validated['reason'],
                ],
                adminId: $admin?->id
            );

            return redirect()
                ->back()
                ->with('status', "Stock adjusted for [{$variant->sku}]: {$result['previous_qty']} → {$result['new_qty']} ({$validated['reason']}).");
        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->withErrors(['inventory' => $e->getMessage()]);
        }
    }
}
