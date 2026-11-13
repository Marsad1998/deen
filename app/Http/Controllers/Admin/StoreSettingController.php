<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StoreSettingController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Display the Store Settings management form.
     */
    public function index(): View
    {
        $settings = [
            'store_name'       => StoreSetting::get('store_name', 'DEEN Electronics'),
            'store_tagline'    => StoreSetting::get('store_tagline', 'Lahore\'s Trusted Home Appliances & Electronics Showroom'),
            'store_logo'       => StoreSetting::get('store_logo'),
            'whatsapp_number'  => StoreSetting::get('whatsapp_number', '0300-7769932'),
            'contact_phone'    => StoreSetting::get('contact_phone', '0300-7769932'),
            'contact_email'    => StoreSetting::get('contact_email', 'info@deenelectronics.com'),
            'store_address'    => StoreSetting::get('store_address', 'DEEN Electronics, Main Showroom Market, Lahore, Pakistan'),
            'sales_rep_name'   => StoreSetting::get('sales_rep_name', 'Muhammad Aslam'),
            'order_confirmation_msg' => StoreSetting::get('order_confirmation_msg', 'Thank you for choosing DEEN Electronics! Our Lahore showroom desk has received your order and will contact you via WhatsApp shortly.'),
        ];

        return view('admin.settings.store', compact('settings'));
    }

    /**
     * Update store identity, WhatsApp number, and showroom details.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_name'       => ['required', 'string', 'max:100'],
            'store_tagline'    => ['nullable', 'string', 'max:255'],
            'whatsapp_number'  => ['required', 'string', 'max:50'],
            'contact_phone'    => ['required', 'string', 'max:50'],
            'contact_email'    => ['nullable', 'email', 'max:150'],
            'store_address'    => ['nullable', 'string', 'max:500'],
            'sales_rep_name'   => ['nullable', 'string', 'max:100'],
            'order_confirmation_msg' => ['nullable', 'string', 'max:1000'],
            'store_logo_file'  => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
        ]);

        $admin = Auth::guard('admin')->user();
        $changes = [];

        // Save simple string settings
        $fields = [
            'store_name',
            'store_tagline',
            'whatsapp_number',
            'contact_phone',
            'contact_email',
            'store_address',
            'sales_rep_name',
            'order_confirmation_msg',
        ];

        foreach ($fields as $field) {
            $val = $validated[$field] ?? null;
            $old = StoreSetting::get($field);
            if ($old !== $val) {
                StoreSetting::set($field, $val, 'general');
                $changes[$field] = ['old' => $old, 'new' => $val];
            }
        }

        // Handle Logo Upload
        if ($request->hasFile('store_logo_file')) {
            $file = $request->file('store_logo_file');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('store', $filename, 'public');
            $logoUrl = '/storage/' . $path;

            $oldLogo = StoreSetting::get('store_logo');
            StoreSetting::set('store_logo', $logoUrl, 'general');
            $changes['store_logo'] = ['old' => $oldLogo, 'new' => $logoUrl];
        }

        if (! empty($changes)) {
            $this->auditLogService->log(
                action: 'store_settings.updated',
                entity: 'StoreSetting',
                entityId: null,
                changes: $changes,
                adminId: $admin?->id
            );
        }

        return redirect()
            ->route('admin.settings.store')
            ->with('success', 'Store settings and WhatsApp configuration updated successfully.');
    }
}
