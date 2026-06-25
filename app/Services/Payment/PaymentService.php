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

    public static function fromDonation(Donation $donation): self
    {
        $gateway = $donation->paymentMethod?->gateway;
        if (! $gateway) {
            throw new RuntimeException('لا توجد بوابة دفع مرتبطة بطريقة الدفع هذه');
        }
        return new static($gateway);
    }

    // ponytail: 6 init* methods collapsed into match; each was identical pattern
    public function initPayment(Donation $donation): array
    {
        return match ($this->gateway->driver) {
            'stripe' => $this->redirectResult((new StripeService($this->config))->createCheckoutSession($donation), 'Stripe'),
            'paypal' => (fn($url) => $url ? $this->redirectResult($url, 'PayPal') : throw new RuntimeException('فشل الاتصال ببوابة PayPal'))((new PayPalService($this->config))->createOrder($donation)),
            'bank_transfer' => ['type' => 'instructions', 'data' => (new BankTransferService($this->config))->process($donation), 'message' => 'يرجى تحويل المبلغ إلى الحساب البنكي'],
            'wise' => ['type' => 'instructions', 'data' => (new WiseService($this->config))->process($donation), 'message' => 'يرجى تحويل المبلغ عبر Wise'],
            'crypto' => ['type' => 'instructions', 'data' => (new CryptoService($this->config))->process($donation), 'message' => 'يرجى تحويل العملة الرقمية إلى عنوان المحفظة'],
            'manual' => ['type' => 'instructions', 'data' => (new ManualService($this->config))->process($donation), 'message' => 'سيتم التواصل معك لتأكيد التبرع'],
            default => throw new RuntimeException("بوابة دفع غير مدعومة: {$this->gateway->driver}"),
        };
    }

    private function redirectResult(string $url, string $name): array
    {
        return ['type' => 'redirect', 'url' => $url, 'message' => "جاري تحويلك إلى بوابة الدفع {$name}..."];
    }
}
