<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function success(Request $request, string $locale, Donation $donation)
    {
        return view('payment.success', compact('donation'));
    }

    public function cancel(Request $request, string $locale, Donation $donation)
    {
        return view('payment.cancel', compact('donation'));
    }

    public function instructions(Request $request, string $locale, Donation $donation)
    {
        $this->verifyAccessToken($donation);

        $paymentMethod = $donation->paymentMethod;
        $gateway = $paymentMethod?->gateway;
        $config = $gateway?->config ?? [];

        $instructions = $paymentMethod?->instructions ?? '';
        $driver = $gateway?->driver ?? '';

        return view('payment.instructions', compact('donation', 'config', 'instructions', 'paymentMethod', 'driver'));
    }
}
