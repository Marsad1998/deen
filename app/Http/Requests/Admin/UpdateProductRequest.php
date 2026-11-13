<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->hasPermission('products.edit') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge([
                'slug' => Str::slug($this->input('name')),
            ]);
        }
    }

    public function rules(): array
    {
        $productId = $this->route('product') instanceof \App\Models\Product
            ? $this->route('product')->id
            : (int) $this->route('product');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
            'sku'  => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'brand_id'    => ['nullable', 'integer', 'exists:brands,id'],
            'badge_text'  => ['nullable', 'string', 'max:150'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'full_description'  => ['nullable', 'string'],
            'warranty_text'     => ['nullable', 'string', 'max:255'],
            'meta_title'        => ['nullable', 'string', 'max:255'],
            'meta_description'  => ['nullable', 'string', 'max:500'],
            'meta_keywords'     => ['nullable', 'string', 'max:255'],
            'is_featured'       => ['nullable', 'boolean'],
            'status'            => ['required', 'string', Rule::in(['draft', 'active', 'archived'])],

            // Pricing/Stock for default variant when updated directly from basic product form
            'price'          => ['nullable', 'numeric', 'min:0'],
            'original_price' => ['nullable', 'numeric', 'min:0', 'gte:price'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
