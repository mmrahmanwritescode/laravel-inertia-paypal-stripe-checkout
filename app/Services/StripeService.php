<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\Order;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class StripeService
{
    protected $secretKey;
    protected $webhookSecret;

    public function __construct()
    {
        $this->secretKey = config('services.stripe.secret');
        $this->webhookSecret = config('services.stripe.webhook_secret');
        
        // Set Stripe API key
        Stripe::setApiKey($this->secretKey);
    }

    public function createPaymentIntent($amount, $currency = 'usd', $metadata = [])
    {
        try {
            // Convert amount to cents (Stripe expects smallest currency unit)
            $amountInCents = intval($amount * 100);
            
            $paymentIntent = PaymentIntent::create([
                'amount' => $amountInCents,
                'currency' => $currency,
                'metadata' => $metadata,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return [
                'id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
            ];
        } catch (\Exception $e) {
            Log::error('Stripe Payment Intent creation failed: ' . $e->getMessage());
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    public function verifyWebhook($payload, $sigHeader)
    {
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, 
                $sigHeader, 
                $this->webhookSecret
            );
            return true;
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid Stripe webhook payload: ' . $e->getMessage());
            return false;
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid Stripe webhook signature: ' . $e->getMessage());
            return false;
        }
    }

    public function handlePaymentSucceeded($event)
    {
        // Update order status in DB
        $intent = $event['data']['object'] ?? [];
        $order = Order::where('payment_intent_id', $intent['id'] ?? null)->first();
        if ($order) {
            $order->update(['status' => 'confirmed', 'payment_method' => 'stripe']);
            Log::info('Order confirmed for Stripe payment: ' . $order->id);
        }
    }

    public function handlePaymentFailed($event)
    {
        $intent = $event['data']['object'] ?? [];
        $order = Order::where('payment_intent_id', $intent['id'] ?? null)->first();
        if ($order) {
            $order->update(['status' => 'cancelled', 'payment_method' => 'stripe']);
            Log::info('Order cancelled for Stripe payment: ' . $order->id);
        }
    }

    public function handlePaymentCanceled($event)
    {
        $intent = $event['data']['object'] ?? [];
        $order = Order::where('payment_intent_id', $intent['id'] ?? null)->first();
        if ($order) {
            $order->update(['status' => 'cancelled', 'payment_method' => 'stripe']);
            Log::info('Order cancelled for Stripe payment: ' . $order->id);
        }
    }
}
