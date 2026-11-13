<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductSpecification;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductSpecificationController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Add a dynamic key/value specification.
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'spec_key'           => ['required', 'string', 'max:150'],
            'spec_value'         => ['required', 'string'],
            'spec_group'         => ['nullable', 'string', 'max:100'],
            'sort_order'         => ['nullable', 'integer'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
        ]);

        $variantId = ! empty($validated['product_variant_id']) ? (int) $validated['product_variant_id'] : null;
        $productId = $variantId ? null : $product->id;

        $maxSort = ProductSpecification::where('product_id', $product->id)->max('sort_order');

        $spec = ProductSpecification::create([
            'product_id'         => $productId,
            'product_variant_id' => $variantId,
            'spec_group'         => $validated['spec_group'] ?? null,
            'spec_key'           => trim($validated['spec_key']),
            'spec_value'         => trim($validated['spec_value']),
            'sort_order'         => $validated['sort_order'] ?? (($maxSort ?? 0) + 1),
        ]);

        $this->auditLogService->log(
            action: 'specification.added',
            entity: 'ProductSpecification',
            entityId: $spec->id,
            changes: [
                'key'   => $spec->spec_key,
                'value' => $spec->spec_value,
            ],
            adminId: Auth::guard('admin')->id()
        );

        return redirect()
            ->route('admin.products.edit', $product->id)
            ->with('status', "Specification [{$spec->spec_key}] added.");
    }

    /**
     * Remove a dynamic specification.
     */
    public function destroy(Product $product, ProductSpecification $specification): RedirectResponse
    {
        $key = $specification->spec_key;
        $specification->delete();

        return redirect()
            ->route('admin.products.edit', $product->id)
            ->with('status', "Specification [{$key}] removed.");
    }
}
