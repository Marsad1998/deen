<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdjustInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->hasPermission('inventory.adjust') ?? false;
    }

    public function rules(): array
    {
        return [
            'change_qty' => ['required', 'integer', 'not_in:0'],
            'reason'     => ['required', 'string', 'max:100'],
        ];
    }
}
