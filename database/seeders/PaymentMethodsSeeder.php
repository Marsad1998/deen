<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

class PaymentMethodsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Payment Methods
        $methods = [
            [
                'code'           => 'cod',
                'name'           => 'Cash on Delivery (COD)',
                'subtitle'       => 'Pay cash upon delivery at your doorstep across Lahore & Pakistan',
                'is_enabled'     => true,
                'requires_proof' => false,
                'account_title'  => null,
                'account_number' => null,
                'bank_name'      => null,
                'branch_name'    => null,
                'iban'           => null,
                'instructions'   => 'Please keep the exact cash amount ready for the delivery courier agent upon arrival.',
                'display_order'  => 1,
            ],
            [
                'code'           => 'jazzcash',
                'name'           => 'JazzCash Mobile Account',
                'subtitle'       => 'Instant mobile transfer to our official JazzCash account',
                'is_enabled'     => true,
                'requires_proof' => true,
                'account_title'  => 'Muhammad Aslam (DEEN Electronics)',
                'account_number' => '0300-7769932',
                'bank_name'      => null,
                'branch_name'    => null,
                'iban'           => null,
                'instructions'   => 'Transfer the order amount to JazzCash account 0300-7769932 (Muhammad Aslam) and attach your payment receipt screenshot before completing checkout.',
                'display_order'  => 2,
            ],
            [
                'code'           => 'easypaisa',
                'name'           => 'Easypaisa Mobile Account',
                'subtitle'       => 'Instant mobile transfer via Easypaisa App or mobile account',
                'is_enabled'     => true,
                'requires_proof' => true,
                'account_title'  => 'Muhammad Aslam (DEEN Electronics)',
                'account_number' => '0300-7769932',
                'bank_name'      => null,
                'branch_name'    => null,
                'iban'           => null,
                'instructions'   => 'Transfer the order amount to Easypaisa account 0300-7769932 (Muhammad Aslam) and attach your payment receipt screenshot before completing checkout.',
                'display_order'  => 3,
            ],
            [
                'code'           => 'bank_transfer',
                'name'           => 'Direct Bank Transfer',
                'subtitle'       => 'Online IBFT or showroom deposit to our official bank account',
                'is_enabled'     => true,
                'requires_proof' => true,
                'account_title'  => 'DEEN Electronics Showroom',
                'account_number' => '0102-0103984712',
                'bank_name'      => 'Meezan Bank Limited',
                'branch_name'    => 'Gulberg III Main Branch, Lahore',
                'iban'           => 'PK12MEZN0001020103984712',
                'instructions'   => 'Transfer the order amount via IBFT to Meezan Bank, Account # 0102-0103984712 (DEEN Electronics Showroom) and attach your payment receipt screenshot below.',
                'display_order'  => 4,
            ],
            [
                'code'           => 'payfast',
                'name'           => 'PayFast Debit/Credit Card (Visa / Mastercard)',
                'subtitle'       => 'Secure online card payment gateway (Coming Soon)',
                'is_enabled'     => false,
                'requires_proof' => false,
                'account_title'  => null,
                'account_number' => null,
                'bank_name'      => null,
                'branch_name'    => null,
                'iban'           => null,
                'instructions'   => 'Online card payments via Visa and Mastercard (PayFast gateway currently undergoing merchant integration).',
                'display_order'  => 5,
            ],
        ];

        foreach ($methods as $data) {
            PaymentMethod::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }

        // 2. Seed Default Store Settings (Logo, WhatsApp, Showroom details)
        $storeDefaults = [
            'store_name'            => 'DEEN Electronics',
            'store_tagline'         => 'Lahore\'s Trusted Home Appliances & Electronics Showroom',
            'store_logo'            => null,
            'whatsapp_number'       => '0300-7769932',
            'contact_phone'         => '0300-7769932',
            'contact_email'         => 'info@deenelectronics.com',
            'store_address'         => 'DEEN Electronics, Main Showroom Market, Lahore, Pakistan',
            'sales_rep_name'        => 'Muhammad Aslam',
            'order_confirmation_msg'=> 'Thank you for choosing DEEN Electronics! Our Lahore showroom desk has received your order and will contact you via WhatsApp shortly.',
        ];

        foreach ($storeDefaults as $key => $val) {
            if (StoreSetting::get($key) === null) {
                StoreSetting::set($key, $val, 'general');
            }
        }
    }
}
