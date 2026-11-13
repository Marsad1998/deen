<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UploadProductMediaRequest;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Services\AuditLogService;
use App\Services\MediaStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ProductMediaController extends Controller
{
    public function __construct(
        protected MediaStorageService $mediaService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Upload and attach a new media asset to a product or variant.
     */
    public function upload(UploadProductMediaRequest $request, Product $product): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $admin = Auth::guard('admin')->user();

        $variantId = ! empty($validated['product_variant_id']) ? (int) $validated['product_variant_id'] : null;
        $productId = $variantId ? null : $product->id;

        try {
            $media = $this->mediaService->storeMedia(
                file: $request->file('file'),
                productId: $productId,
                variantId: $variantId,
                altText: $validated['alt_text'] ?? $product->name,
                isPrimary: (bool) ($validated['is_primary'] ?? false)
            );

            $this->auditLogService->log(
                action: 'media.uploaded',
                entity: 'ProductMedia',
                entityId: $media->id,
                changes: [
                    'url'        => $media->url,
                    'is_primary' => $media->is_primary,
                ],
                adminId: $admin?->id
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'media'   => $media,
                ]);
            }

            return redirect()
                ->route('admin.products.edit', $product->id)
                ->with('status', 'Image uploaded successfully.');
        } catch (Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()
                ->back()
                ->withErrors(['media' => $e->getMessage()]);
        }
    }

    /**
     * Set a media item as the primary thumbnail.
     */
    public function setPrimary(Product $product, ProductMedia $media): RedirectResponse
    {
        $this->mediaService->setPrimary($media);

        $this->auditLogService->log(
            action: 'media.set_primary',
            entity: 'ProductMedia',
            entityId: $media->id,
            changes: ['product_id' => $product->id],
            adminId: Auth::guard('admin')->id()
        );

        return redirect()
            ->route('admin.products.edit', $product->id)
            ->with('status', 'Primary thumbnail updated.');
    }

    /**
     * Reorder gallery media.
     */
    public function reorder(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        $orderedIds = $request->input('ordered_ids', []);
        if (! empty($orderedIds) && is_array($orderedIds)) {
            $this->mediaService->reorder($orderedIds);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back();
    }

    /**
     * Delete a media item and clean up the file on disk.
     */
    public function destroy(Product $product, ProductMedia $media): RedirectResponse|JsonResponse
    {
        $mediaId = $media->id;
        $this->mediaService->deleteMedia($media);

        $this->auditLogService->log(
            action: 'media.deleted',
            entity: 'ProductMedia',
            entityId: $mediaId,
            changes: ['product_id' => $product->id],
            adminId: Auth::guard('admin')->id()
        );

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('admin.products.edit', $product->id)
            ->with('status', 'Image removed.');
    }
}
