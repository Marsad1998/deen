<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->hasPermission('products.create') ?? false;
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:products,slug'],
            'sku'  => ['nullable', 'string', 'max:100', 'unique:products,sku', 'unique:product_variants,sku'],
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
            
            // Initial/Default Variant Details (required for sellable catalog entry)
            'price'          => ['required', 'numeric', 'min:0'],
            'original_price' => ['nullable', 'numeric', 'min:0', 'gte:price'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'variant_name'   => ['nullable', 'string', 'max:255'],

            // Optional initial dynamic specifications
            'specifications'               => ['nullable', 'array'],
            'specifications.*.spec_key'    => ['required_with:specifications.*.spec_value', 'string', 'max:150'],
            'specifications.*.spec_value'  => ['required_with:specifications.*.spec_key', 'string'],
            'specifications.*.spec_group'  => ['nullable', 'string', 'max:100'],
        ];
    }
}
