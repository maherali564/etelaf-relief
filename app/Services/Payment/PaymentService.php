<?php

namespace App\Services\Payment;

use App\Models\Donation;
use App\Models\PaymentGateway;
use RuntimeException;

class PaymentService
{
    protected ?PaymentGateway $gateway;

    protected array $config;

    public function __construct(PaymentGateway $gateway)
    {
        $this->gateway = $gateway;
        $this->config = $gateway->config ?? [];
    }

    /**
     * Create a PaymentService instance from a donation's payment method
     *
     * @param  Donation  $donation  The donation with associated payment method
     *
     * @throws RuntimeException If no gateway is associated
     */
    public static function fromDonation(Donation $donation): self
    {
        $gateway = $donation->paymentMethod?->gateway;

        if (! $gateway) {
            throw new RuntimeException('لا توجد بوابة دفع مرتبطة بطريقة الدفع هذه');
        }

        return new static($gateway);
    }

    /**
     * Initialize payment for a donation using the configured gateway
     *
     * @param  Donation  $donation  The donation to process
     * @return array Payment result with 'type', 'url' or 'data', and 'message'
     *
     * @throws RuntimeException For unsupported drivers
     */
    public function initPayment(Donation $donation): array
    {
        $driver = $this->gateway->driver;

        return match ($driver) {
            'stripe' => $this->initStripe($donation),
            'paypal' => $this->initPayPal($donation),
            'bank_transfer' => $this->initBankTransfer($donation),
            'wise' => $this->initWise($donation),
            'crypto' => $this->initCrypto($donation),
            'manual' => $this->initManual($donation),
            default => throw new RuntimeException("بوابة دفع غير مدعومة: $driver"),
        };
    }

    protected function initStripe(Donation $donation): array
    {
        $service = new StripeService($this->config);
        $url = $service->createCheckoutSession($donation);

        return [
            'type' => 'redirect',
            'url' => $url,
            'message' => 'جاري تحويلك إلى بوابة الدفع Stripe...',
        ];
    }

    protected function initPayPal(Donation $donation): array
    {
        $service = new PayPalService($this->config);
        $url = $service->createOrder($donation);

        if (! $url) {
            throw new RuntimeException('فشل الاتصال ببوابة PayPal');
        }

        return [
            'type' => 'redirect',
            'url' => $url,
            'message' => 'جاري تحويلك إلى بوابة الدفع PayPal...',
        ];
    }

    protected function initWise(Donation $donation): array
    {
        $service = new WiseService($this->config);

        return [
            'type' => 'instructions',
            'data' => $service->process($donation),
            'message' => 'يرجى تحويل المبلغ عبر Wise',
        ];
    }

    protected function initBankTransfer(Donation $donation): array
    {
        $service = new BankTransferService($this->config);

        return [
            'type' => 'instructions',
            'data' => $service->process($donation),
            'message' => 'يرجى تحويل المبلغ إلى الحساب البنكي',
        ];
    }

    protected function initCrypto(Donation $donation): array
    {
        $service = new CryptoService($this->config);

        return [
            'type' => 'instructions',
            'data' => $service->process($donation),
            'message' => 'يرجى تحويل العملة الرقمية إلى عنوان المحفظة',
        ];
    }

    protected function initManual(Donation $donation): array
    {
        $service = new ManualService($this->config);

        return [
            'type' => 'instructions',
            'data' => $service->process($donation),
            'message' => 'سيتم التواصل معك لتأكيد التبرع',
        ];
    }
}
