<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

class StoreSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds idempotently.
     */
    public function run(): void
    {
        $settings = [
            // General Store Identity
            [
                'setting_group' => 'general',
                'setting_key'   => 'store_name',
                'setting_value' => 'DEEN Electronics',
            ],
            [
                'setting_group' => 'general',
                'setting_key'   => 'store_tagline',
                'setting_value' => 'Premium Electronics & Charging Accessories',
            ],
            [
                'setting_group' => 'general',
                'setting_key'   => 'store_email',
                'setting_value' => 'support@deenelectronics.com',
            ],
            [
                'setting_group' => 'general',
                'setting_key'   => 'store_phone',
                'setting_value' => '+92-300-0000000',
            ],
            [
                'setting_group' => 'general',
                'setting_key'   => 'store_currency',
                'setting_value' => 'PKR',
            ],
            [
                'setting_group' => 'general',
                'setting_key'   => 'store_currency_symbol',
                'setting_value' => 'Rs.',
            ],
            [
                'setting_group' => 'general',
                'setting_key'   => 'store_timezone',
                'setting_value' => 'Asia/Karachi',
            ],

            // System & Operations Flags (foundational placeholders only)
            [
                'setting_group' => 'system',
                'setting_key'   => 'maintenance_mode',
                'setting_value' => '0',
            ],
            [
                'setting_group' => 'system',
                'setting_key'   => 'guest_checkout_enabled',
                'setting_value' => '1',
            ],
        ];

        foreach ($settings as $setting) {
            StoreSetting::updateOrCreate(
                ['setting_key' => $setting['setting_key']],
                [
                    'setting_group' => $setting['setting_group'],
                    'setting_value' => $setting['setting_value'],
                ]
            );
        }
    }
}
