<?php

namespace App\Services;

use App\Models\ProductMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class MediaStorageService
{
    /**
     * Allowed MIME types for uploaded product media.
     */
    protected array $allowedMimeTypes = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /**
     * Store an uploaded image safely and create a ProductMedia record.
     */
    public function storeMedia(
        UploadedFile $file,
        ?int $productId = null,
        ?int $variantId = null,
        ?string $altText = null,
        bool $isPrimary = false
    ): ProductMedia {
        if (($productId && $variantId) || (! $productId && ! $variantId)) {
            throw new InvalidArgumentException('Media must belong to either a product or a variant, exclusively.');
        }

        // Validate MIME type against whitelist
        $mime = $file->getMimeType();
        if (! in_array($mime, $this->allowedMimeTypes, true)) {
            throw new InvalidArgumentException("Invalid file type: [{$mime}]. Allowed types: JPG, PNG, WEBP.");
        }

        // Verify actual image integrity
        $imageInfo = @getimagesize($file->getRealPath());
        if ($imageInfo === false) {
            throw new InvalidArgumentException('Uploaded file is not a valid image.');
        }

        // Generate safe, collision-resistant random filename
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $extension = 'jpg';
        }
        $safeFilename = Str::uuid()->toString() . '.' . $extension;

        // Path organization
        $subfolder = $productId ? "products/{$productId}" : "variants/{$variantId}";
        $storagePath = "uploads/{$subfolder}";

        // Save file to public disk
        $path = $file->storeAs($storagePath, $safeFilename, 'public');
        if (! $path) {
            throw new RuntimeException('Failed to store file on disk.');
        }

        $url = Storage::disk('public')->url($path);

        return DB::transaction(function () use ($productId, $variantId, $url, $altText, $isPrimary) {
            // If marked as primary, demote existing primary media for this owner
            if ($isPrimary) {
                if ($productId) {
                    ProductMedia::where('product_id', $productId)->update(['is_primary' => false]);
                } else {
                    ProductMedia::where('product_variant_id', $variantId)->update(['is_primary' => false]);
                }
            }

            // Get next sort order
            $maxSort = $productId
                ? ProductMedia::where('product_id', $productId)->max('sort_order')
                : ProductMedia::where('product_variant_id', $variantId)->max('sort_order');

            $media = ProductMedia::create([
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'media_type' => 'image',
                'url' => $url,
                'alt_text' => $altText,
                'sort_order' => ($maxSort ?? 0) + 1,
                'is_primary' => $isPrimary,
            ]);

            // If this is the product's only/primary image, update products.main_image as fallback
            if ($productId && ($isPrimary || ProductMedia::where('product_id', $productId)->count() === 1)) {
                DB::table('products')->where('id', $productId)->update([
                    'main_image' => $url,
                    'updated_at' => now(),
                ]);
            }

            return $media;
        });
    }

    /**
     * Delete a media record and remove its underlying physical file from disk.
     */
    public function deleteMedia(ProductMedia $media): void
    {
        DB::transaction(function () use ($media) {
            $url = $media->url;
            $productId = $media->product_id;
            $wasPrimary = $media->is_primary;

            // Extract relative disk path from URL
            // e.g., URL /storage/uploads/products/1/xyz.jpg -> uploads/products/1/xyz.jpg
            $relativePath = str_replace(Storage::disk('public')->url(''), '', $url);
            $relativePath = ltrim($relativePath, '/');

            // Delete record
            $media->delete();

            // Delete physical file
            if ($relativePath && Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }

            // If deleted media was primary, promote the next available image
            if ($wasPrimary && $productId) {
                $nextMedia = ProductMedia::where('product_id', $productId)->orderBy('sort_order')->first();
                if ($nextMedia) {
                    $nextMedia->update(['is_primary' => true]);
                    DB::table('products')->where('id', $productId)->update([
                        'main_image' => $nextMedia->url,
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('products')->where('id', $productId)->update([
                        'main_image' => null,
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    /**
     * Set a media item as primary for its owning product/variant.
     */
    public function setPrimary(ProductMedia $media): void
    {
        DB::transaction(function () use ($media) {
            if ($media->product_id) {
                ProductMedia::where('product_id', $media->product_id)->update(['is_primary' => false]);
                $media->update(['is_primary' => true]);
                DB::table('products')->where('id', $media->product_id)->update([
                    'main_image' => $media->url,
                    'updated_at' => now(),
                ]);
            } elseif ($media->product_variant_id) {
                ProductMedia::where('product_variant_id', $media->product_variant_id)->update(['is_primary' => false]);
                $media->update(['is_primary' => true]);
            }
        });
    }

    /**
     * Reorder media items based on an ordered array of IDs.
     */
    public function reorder(array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                ProductMedia::where('id', $id)->update(['sort_order' => $index + 1]);
            }
        });
    }
}
