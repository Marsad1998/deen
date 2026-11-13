<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->hasPermission('products.edit') ?? false;
    }

    public function rules(): array
    {
        $variantId = $this->route('variant') instanceof \App\Models\ProductVariant
            ? $this->route('variant')->id
            : (int) $this->route('variant');

        return [
            'sku'            => ['required', 'string', 'max:100', Rule::unique('product_variants', 'sku')->ignore($variantId)],
            'name'           => ['required', 'string', 'max:255'],
            'price'          => ['required', 'numeric', 'min:0'],
            'original_price' => ['nullable', 'numeric', 'min:0', 'gte:price'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'stock_status'   => ['nullable', 'string', Rule::in(['in_stock', 'out_of_stock', 'backorder'])],
            'attributes'     => ['nullable', 'array'],
            'is_active'      => ['nullable', 'boolean'],
            'price_change_reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
