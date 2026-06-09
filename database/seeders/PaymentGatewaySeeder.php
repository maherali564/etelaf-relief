<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        // Stripe
        $stripe = PaymentGateway::create([
            'name' => 'Stripe',
            'slug' => 'stripe',
            'driver' => 'stripe',
            'type' => 'traditional',
            'config' => [
                'publishable_key' => '',
                'secret_key' => '',
                'webhook_secret' => '',
            ],
            'supported_currencies' => ['USD', 'EUR', 'GBP'],
            'min_amount' => 1,
            'max_amount' => 50000,
            'processing_fee' => 2.9,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $stripe->paymentMethods()->create([
            'name' => 'بطاقة ائتمان / خصم',
            'description' => 'الدفع عبر بطاقة فيزا أو ماستركارد',
            'icon' => '💳',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // PayPal
        $paypal = PaymentGateway::create([
            'name' => 'PayPal',
            'slug' => 'paypal',
            'driver' => 'paypal',
            'type' => 'traditional',
            'config' => [
                'client_id' => '',
                'client_secret' => '',
                'mode' => 'sandbox',
            ],
            'supported_currencies' => ['USD', 'EUR', 'GBP'],
            'min_amount' => 1,
            'max_amount' => 50000,
            'processing_fee' => 2.9,
            'sort_order' => 2,
            'is_active' => true,
        ]);
        $paypal->paymentMethods()->create([
            'name' => 'PayPal',
            'description' => 'الدفع عبر حساب PayPal',
            'icon' => '🅿️',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Bank Transfer
        $bank = PaymentGateway::create([
            'name' => 'تحويل بنكي',
            'slug' => 'bank-transfer',
            'driver' => 'bank_transfer',
            'type' => 'bank_transfer',
            'config' => [
                'bank_name' => '',
                'account_name' => '',
                'account_number' => '',
                'iban' => '',
                'swift_code' => '',
            ],
            'supported_currencies' => ['USD', 'EUR', 'SAR'],
            'min_amount' => 10,
            'sort_order' => 3,
            'is_active' => true,
        ]);
        $bank->paymentMethods()->create([
            'name' => 'تحويل بنكي مباشر',
            'description' => 'التحويل المباشر إلى الحساب البنكي',
            'icon' => '🏦',
            'instructions' => 'يرجى تحويل المبلغ إلى الحساب البنكي المذكور أدناه والتواصل معنا لتأكيد التبرع.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Crypto
        $crypto = PaymentGateway::create([
            'name' => 'عملات رقمية',
            'slug' => 'crypto',
            'driver' => 'crypto',
            'type' => 'crypto',
            'config' => [
                'wallet_address' => '',
                'network' => 'TRC20',
                'conversion_rate' => 1,
                'currency_symbol' => 'USDT',
            ],
            'supported_currencies' => ['USD'],
            'min_amount' => 10,
            'sort_order' => 4,
            'is_active' => true,
        ]);
        $crypto->paymentMethods()->create([
            'name' => 'عملات رقمية',
            'description' => 'الدفع باستخدام العملات الرقمية (USDT، BTC، ETH)',
            'icon' => '₿',
            'instructions' => 'يرجى تحويل المبلغ بالعملة الرقمية إلى عنوان المحفظة المذكور أدناه.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Wise
        $wise = PaymentGateway::create([
            'name' => 'Wise',
            'slug' => 'wise',
            'driver' => 'wise',
            'type' => 'bank_transfer',
            'config' => [
                'api_token' => '',
                'profile_id' => '',
                'webhook_secret' => '',
                'bank_name' => 'Wise',
                'account_name' => '',
                'iban' => '',
                'swift_code' => '',
            ],
            'supported_currencies' => ['USD', 'EUR', 'GBP'],
            'min_amount' => 1,
            'max_amount' => 50000,
            'processing_fee' => 0.5,
            'sort_order' => 5,
            'is_active' => false,
        ]);
        $wise->paymentMethods()->create([
            'name' => 'تحويل عبر Wise',
            'description' => 'التحويل عبر Wise (TransferWise)',
            'icon' => '💱',
            'instructions' => 'يرجى تحويل المبلغ عبر Wise إلى الحساب المذكور أدناه.',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }
}
