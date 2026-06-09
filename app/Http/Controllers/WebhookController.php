<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Models\PaymentGateway;
use App\Services\Payment\IdempotencyHelper;
use App\Services\Payment\PayPalService;
use App\Services\Payment\StripeService;
use App\Services\Payment\WiseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function stripe(Request $request): JsonResponse
    {
        $gateway = $this->loadActiveGateway('stripe');
        if (! $gateway) {
            return response()->json(['error' => 'Gateway not found'], 404);
        }

        $service = new StripeService($gateway->config ?? []);
        $signature = $request->header('Stripe-Signature') ?? '';

        if (empty($signature)) {
            Log::warning('Stripe webhook: missing signature header');

            return response()->json(['error' => 'Missing signature'], 400);
        }

        try {
            $event = $service->verifyWebhook($request->getContent(), $signature);
        } catch (\Exception $e) {
            Log::warning('Stripe webhook: '.$e->getMessage());

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $this->logWebhook('stripe', $event['type'] ?? '', $request->getContent());

        match ($event['type'] ?? '') {
            'checkout.session.completed' => $this->handleStripeCheckoutCompleted($event),
            'invoice.paid' => $this->handleStripeInvoicePaid($event),
            'customer.subscription.deleted' => $this->handleStripeSubscriptionDeleted($event),
            default => null,
        };

        return response()->json(['status' => 'ok']);
    }

    public function paypal(Request $request): JsonResponse
    {
        $gateway = $this->loadActiveGateway('paypal');
        if (! $gateway) {
            return response()->json(['error' => 'Gateway not found'], 404);
        }

        $service = new PayPalService($gateway->config ?? []);
        $payload = $request->getContent();
        $headers = $request->header();
        $verified = $service->verifyWebhook($payload, $headers);

        if (! $verified) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $data = json_decode($payload, true);
        $eventType = $data['event_type'] ?? '';

        $this->logWebhook('paypal', $eventType, $payload);

        match ($eventType) {
            'CHECKOUT.ORDER.APPROVED' => $this->handlePaypalOrderApproved($data, $gateway),
            'PAYMENT.SALE.COMPLETED' => $this->handlePaypalSaleCompleted($data),
            default => null,
        };

        return response()->json(['status' => 'ok']);
    }

    private function loadActiveGateway(string $driver): ?PaymentGateway
    {
        $gateway = PaymentGateway::where('driver', $driver)->where('is_active', true)->first();

        if (! $gateway) {
            Log::warning("{$driver} webhook: active gateway not found");
        }

        return $gateway;
    }

    private function logWebhook(string $provider, string $type, ?string $payload = null): void
    {
        $context = ['type' => $type];
        if ($payload) {
            $decoded = json_decode($payload, true);
            $safePayload = $decoded;
            if (isset($safePayload['data']['object']['card'])) {
                unset($safePayload['data']['object']['card']);
            }
            $context['payload'] = $safePayload;
        }

        Log::info("{$provider} webhook received", $context);

        activity()
            ->withProperties(['provider' => $provider, 'event_type' => $type, 'payload' => $context['payload'] ?? null])
            ->log("webhook_received:{$provider}");
    }

    private function handleStripeCheckoutCompleted(array $event): void
    {
        $sessionId = $event['data']['object']['id'] ?? '';
        $eventId = $event['id'] ?? '';

        if (empty($sessionId)) {
            return;
        }

        if (! empty($eventId) && IdempotencyHelper::checkAndMark($eventId, Donation::class, 'idempotency_key')) {
            return;
        }

        $donation = Donation::where('transaction_id', $sessionId)->first();

        if (! $donation) {
            Log::warning('Stripe webhook: donation not found', ['session_id' => $sessionId]);

            return;
        }

        if ($donation->status !== 'pending') {
            Log::info('Stripe webhook: donation already processed', ['donation_id' => $donation->id, 'status' => $donation->status]);

            return;
        }

        $webhookAmount = ($event['data']['object']['amount_total'] ?? 0) / 100;
        $storedAmount = (float) $donation->amount;
        if (abs($webhookAmount - $storedAmount) > 0.01) {
            Log::warning('Stripe webhook: amount mismatch', [
                'donation_id' => $donation->id,
                'webhook_amount' => $webhookAmount,
                'stored_amount' => $storedAmount,
            ]);

            return;
        }

        $updateData = ['status' => 'completed'];
        if (! empty($eventId)) {
            $updateData['idempotency_key'] = $eventId;
        }

        $subscriptionId = $event['data']['object']['subscription'] ?? null;
        if ($subscriptionId) {
            $updateData['stripe_subscription_id'] = $subscriptionId;
        }

        $donation->update($updateData);
        Log::info('Donation completed via Stripe', ['donation_id' => $donation->id]);
    }

    private function handleStripeInvoicePaid(array $event): void
    {
        $subscriptionId = $event['data']['object']['subscription'] ?? '';

        if (empty($subscriptionId)) {
            return;
        }

        $parentDonation = Donation::where('stripe_subscription_id', $subscriptionId)->first();

        if (! $parentDonation) {
            Log::warning('Stripe webhook: parent donation not found for subscription', ['subscription_id' => $subscriptionId]);

            return;
        }

        $amount = ($event['data']['object']['amount_paid'] ?? 0) / 100;

        Donation::create([
            'donor_id' => $parentDonation->donor_id,
            'donor_name' => $parentDonation->donor_name,
            'email' => $parentDonation->email,
            'phone' => $parentDonation->phone,
            'amount' => $amount,
            'currency' => $parentDonation->currency,
            'payment_method_id' => $parentDonation->payment_method_id,
            'transaction_id' => $event['data']['object']['id'] ?? ('inv_'.uniqid('', true)),
            'status' => 'completed',
            'is_recurring' => true,
            'recurring_interval' => $parentDonation->recurring_interval,
            'stripe_subscription_id' => $subscriptionId,
            'campaign_id' => $parentDonation->campaign_id,
            'project_id' => $parentDonation->project_id,
            'post_id' => $parentDonation->post_id,
            'story_id' => $parentDonation->story_id,
            'is_anonymous' => $parentDonation->is_anonymous,
            'locale' => $parentDonation->locale,
            'donated_at' => now(),
        ]);

        Log::info('Recurring donation created via Stripe', ['subscription_id' => $subscriptionId]);
    }

    private function handleStripeSubscriptionDeleted(array $event): void
    {
        $subscriptionId = $event['data']['object']['id'] ?? '';

        if (empty($subscriptionId)) {
            return;
        }

        Donation::where('stripe_subscription_id', $subscriptionId)
            ->where('is_recurring', true)
            ->update(['is_recurring' => false, 'recurring_interval' => null]);

        Log::info('Subscription cancelled via Stripe', ['subscription_id' => $subscriptionId]);
    }

    private function handlePaypalOrderApproved(array $data, PaymentGateway $gateway): void
    {
        $orderId = $data['resource']['id'] ?? '';

        if (empty($orderId)) {
            return;
        }

        $donation = Donation::where('transaction_id', $orderId)->first();

        if (! $donation) {
            Log::warning('PayPal webhook: donation not found', ['order_id' => $orderId]);

            return;
        }

        if ($donation->status !== 'pending') {
            Log::info('PayPal webhook: donation already processed', ['donation_id' => $donation->id, 'status' => $donation->status]);

            return;
        }

        $purchaseUnits = $data['resource']['purchase_units'] ?? [];
        $webhookAmount = (float) ($purchaseUnits[0]['amount']['value'] ?? 0);
        $storedAmount = (float) $donation->amount;
        if ($webhookAmount > 0 && abs($webhookAmount - $storedAmount) > 0.01) {
            Log::warning('PayPal webhook: amount mismatch', [
                'donation_id' => $donation->id,
                'webhook_amount' => $webhookAmount,
                'stored_amount' => $storedAmount,
            ]);

            return;
        }

        if (filled($donation->idempotency_key) && IdempotencyHelper::checkAndMark($donation->idempotency_key.'_capture', Donation::class, 'idempotency_key')) {
            return;
        }

        try {
            $service = new PayPalService($gateway->config ?? []);
            $capture = $service->captureOrder($orderId);

            if (($capture['status'] ?? '') === 'COMPLETED') {
                $donation->update(['status' => 'completed']);
                Log::info('Donation completed via PayPal', ['donation_id' => $donation->id]);
            } else {
                Log::warning('PayPal capture returned non-completed status', [
                    'order_id' => $orderId,
                    'status' => $capture['status'] ?? 'unknown',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('PayPal capture failed', [
                'order_id' => $orderId,
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function handlePaypalSaleCompleted(array $data): void
    {
        $billingToken = $data['resource']['billing_agreement_id'] ?? '';

        if (empty($billingToken)) {
            return;
        }

        $parentDonation = Donation::where('billing_agreement_id', $billingToken)->first();

        if (! $parentDonation) {
            Log::warning('PayPal webhook: parent donation not found', ['billing_agreement_id' => $billingToken]);

            return;
        }

        $amount = $data['resource']['amount']['total'] ?? 0;

        Donation::create([
            'donor_id' => $parentDonation->donor_id,
            'donor_name' => $parentDonation->donor_name,
            'email' => $parentDonation->email,
            'phone' => $parentDonation->phone,
            'amount' => $amount,
            'currency' => $data['resource']['amount']['currency'] ?? 'USD',
            'payment_method_id' => $parentDonation->payment_method_id,
            'transaction_id' => $data['resource']['id'] ?? ('pp_'.uniqid('', true)),
            'status' => 'completed',
            'is_recurring' => true,
            'recurring_interval' => $parentDonation->recurring_interval,
            'billing_agreement_id' => $billingToken,
            'campaign_id' => $parentDonation->campaign_id,
            'project_id' => $parentDonation->project_id,
            'post_id' => $parentDonation->post_id,
            'story_id' => $parentDonation->story_id,
            'is_anonymous' => $parentDonation->is_anonymous,
            'locale' => $parentDonation->locale,
            'donated_at' => now(),
        ]);

        Log::info('Recurring donation created via PayPal', ['billing_agreement_id' => $billingToken]);
    }

    public function wise(Request $request): JsonResponse
    {
        $gateway = $this->loadActiveGateway('wise');
        if (! $gateway) {
            return response()->json(['error' => 'Gateway not found'], 404);
        }

        $service = new WiseService($gateway->config ?? []);
        $payload = $request->getContent();
        $signature = $request->header('X-Wise-Signature', '');
        $event = $service->verifyWebhook($payload, $signature);

        if (empty($event)) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $this->logWebhook('wise', $event['event_type'] ?? '', $payload);

        match ($event['event_type'] ?? '') {
            'transfer.state.change' => $this->handleWiseTransferCompleted($event),
            default => null,
        };

        return response()->json(['status' => 'ok']);
    }

    private function handleWiseTransferCompleted(array $event): void
    {
        $transactionId = $event['data']['id'] ?? '';
        $eventId = $event['id'] ?? ('wise_'.($event['data']['resource']['id'] ?? uniqid('', true)));

        if (empty($transactionId)) {
            return;
        }

        if (IdempotencyHelper::checkAndMark($eventId, Donation::class, 'idempotency_key')) {
            return;
        }

        $donation = Donation::where('transaction_id', $transactionId)->first();

        if (! $donation) {
            Log::warning('Wise webhook: donation not found', ['transaction_id' => $transactionId]);

            return;
        }

        if ($donation->status !== 'pending') {
            return;
        }

        $webhookAmount = (float) ($event['data']['amount'] ?? 0);
        $storedAmount = (float) $donation->amount;
        if ($webhookAmount > 0 && abs($webhookAmount - $storedAmount) > 0.01) {
            Log::warning('Wise webhook: amount mismatch', [
                'donation_id' => $donation->id,
                'webhook_amount' => $webhookAmount,
                'stored_amount' => $storedAmount,
            ]);

            return;
        }

        $donation->update(['status' => 'completed', 'idempotency_key' => $eventId]);
        Log::info('Donation completed via Wise', ['donation_id' => $donation->id, 'event_id' => $eventId]);
    }
}
