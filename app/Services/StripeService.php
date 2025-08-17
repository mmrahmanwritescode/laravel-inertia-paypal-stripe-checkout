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
            // Additional validation
            if (empty($sigHeader)) {
                Log::error('Stripe webhook signature header is empty');
                return false;
            }

            if (empty($this->webhookSecret)) {
                Log::error('Stripe webhook secret is not configured');
                return false;
            }

            $event = \Stripe\Webhook::constructEvent(
                $payload, 
                $sigHeader, 
                $this->webhookSecret
            );
            return true;
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid Stripe webhook payload: ' . $e->getMessage(), [
                'payload_length' => strlen($payload),
                'signature_header' => $sigHeader
            ]);
            return false;
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid Stripe webhook signature: ' . $e->getMessage(), [
                'signature_header' => $sigHeader,
                'webhook_secret_configured' => !empty($this->webhookSecret)
            ]);
            return false;
        }
    }

    public function handlePaymentSucceeded($event)
    {
        // Update order status in DB
        $intent = $event['data']['object'] ?? [];
        $paymentIntentId = $intent['id'] ?? null;
        
        if (!$paymentIntentId) {
            Log::warning('Stripe payment succeeded webhook received without payment intent ID');
            return;
        }
        
        // Try to find order by payment_intent_id (exact match first)
        $order = Order::where('payment_intent_id', $paymentIntentId)->first();
        
        // If not found, try to find by payment_intent_id that might contain client_secret
        if (!$order) {
            $order = Order::where('payment_intent_id', 'LIKE', $paymentIntentId . '%')->first();
        }
        
        if ($order) {
            $order->update([
                'status' => 'confirmed', 
                'payment_method' => 'stripe',
                'payment_intent_id' => $paymentIntentId, // Update with clean ID
                'transaction_id' => $paymentIntentId
            ]);
            Log::info('Order confirmed for Stripe payment: ' . $order->id . ' (PaymentIntent: ' . $paymentIntentId . ')');
        } else {
            Log::warning('Stripe payment succeeded but no matching order found for PaymentIntent: ' . $paymentIntentId);
        }
    }

    public function handlePaymentFailed($event)
    {
        $intent = $event['data']['object'] ?? [];
        $paymentIntentId = $intent['id'] ?? null;
        
        if (!$paymentIntentId) {
            return;
        }
        
        // Try exact match first, then LIKE match for client_secret format
        $order = Order::where('payment_intent_id', $paymentIntentId)->first();
        if (!$order) {
            $order = Order::where('payment_intent_id', 'LIKE', $paymentIntentId . '%')->first();
        }
        
        if ($order) {
            $order->update([
                'status' => 'cancelled', 
                'payment_method' => 'stripe',
                'payment_intent_id' => $paymentIntentId
            ]);
            Log::info('Order cancelled for Stripe payment: ' . $order->id . ' (PaymentIntent: ' . $paymentIntentId . ')');
        }
    }

    public function handlePaymentCanceled($event)
    {
        $intent = $event['data']['object'] ?? [];
        $paymentIntentId = $intent['id'] ?? null;
        
        if (!$paymentIntentId) {
            return;
        }
        
        // Try exact match first, then LIKE match for client_secret format
        $order = Order::where('payment_intent_id', $paymentIntentId)->first();
        if (!$order) {
            $order = Order::where('payment_intent_id', 'LIKE', $paymentIntentId . '%')->first();
        }
        
        if ($order) {
            $order->update([
                'status' => 'cancelled', 
                'payment_method' => 'stripe',
                'payment_intent_id' => $paymentIntentId
            ]);
            Log::info('Order cancelled for Stripe payment: ' . $order->id . ' (PaymentIntent: ' . $paymentIntentId . ')');
        }
    }
}
