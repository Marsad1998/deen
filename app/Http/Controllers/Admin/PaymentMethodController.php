<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Display list of all payment methods (COD, Bank Transfer, JazzCash, Easypaisa, PayFast).
     */
    public function index(): View
    {
        $methods = PaymentMethod::orderBy('display_order')->get();

        return view('admin.payment_methods.index', compact('methods'));
    }

    /**
     * Show form to edit payment method configuration (account numbers, instructions, toggles).
     */
    public function edit(PaymentMethod $paymentMethod): View
    {
        return view('admin.payment_methods.edit', compact('paymentMethod'));
    }

    /**
     * Update payment method account details and customer transfer instructions.
     */
    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'subtitle'       => ['nullable', 'string', 'max:255'],
            'is_enabled'     => ['nullable', 'boolean'],
            'requires_proof' => ['nullable', 'boolean'],
            'account_title'  => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'bank_name'      => ['nullable', 'string', 'max:100'],
            'branch_name'    => ['nullable', 'string', 'max:150'],
            'iban'           => ['nullable', 'string', 'max:100'],
            'instructions'   => ['nullable', 'string', 'max:2000'],
            'display_order'  => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        $admin = Auth::guard('admin')->user();
        $oldSnapshot = $paymentMethod->toArray();

        $paymentMethod->update([
            'name'           => $validated['name'],
            'subtitle'       => $validated['subtitle'] ?? null,
            'is_enabled'     => $request->boolean('is_enabled'),
            'requires_proof' => $request->boolean('requires_proof'),
            'account_title'  => $validated['account_title'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'bank_name'      => $validated['bank_name'] ?? null,
            'branch_name'    => $validated['branch_name'] ?? null,
            'iban'           => $validated['iban'] ?? null,
            'instructions'   => $validated['instructions'] ?? null,
            'display_order'  => (int) $validated['display_order'],
        ]);

        $this->auditLogService->log(
            action: 'payment_method.updated',
            entity: 'PaymentMethod',
            entityId: $paymentMethod->id,
            changes: [
                'code'       => $paymentMethod->code,
                'name'       => $paymentMethod->name,
                'is_enabled' => $paymentMethod->is_enabled,
                'account'    => $paymentMethod->account_number,
            ],
            adminId: $admin?->id
        );

        return redirect()
            ->route('admin.payment-methods.index')
            ->with('success', "Payment method '{$paymentMethod->name}' configuration updated successfully.");
    }

    /**
     * Quick toggle enabled status.
     */
    public function toggle(PaymentMethod $paymentMethod): RedirectResponse
    {
        $paymentMethod->update([
            'is_enabled' => ! $paymentMethod->is_enabled,
        ]);

        $statusText = $paymentMethod->is_enabled ? 'enabled' : 'disabled';

        $this->auditLogService->log(
            action: 'payment_method.toggled',
            entity: 'PaymentMethod',
            entityId: $paymentMethod->id,
            changes: ['is_enabled' => $paymentMethod->is_enabled],
            adminId: Auth::guard('admin')->id()
        );

        return redirect()
            ->route('admin.payment-methods.index')
            ->with('status', "Payment method '{$paymentMethod->name}' is now {$statusText}.");
    }
}
