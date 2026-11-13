<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AddInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->hasPermission('inventory.adjust') ?? false;
    }

    public function rules(): array
    {
        return [
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity'           => ['required', 'integer', 'min:1', 'max:100000'],
            'inward_type'        => ['required', 'string', 'max:100'],
            'reference_number'   => ['nullable', 'string', 'max:100'],
            'supplier_name'      => ['nullable', 'string', 'max:100'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_variant_id.required' => 'Please select a product variant to add inventory for.',
            'product_variant_id.exists'   => 'The selected product variant does not exist in the catalog.',
            'quantity.required'           => 'Please enter the stock quantity received.',
            'quantity.min'                => 'Added inventory quantity must be at least 1 unit.',
            'inward_type.required'        => 'Please select an inward reason or transaction type.',
        ];
    }
}
