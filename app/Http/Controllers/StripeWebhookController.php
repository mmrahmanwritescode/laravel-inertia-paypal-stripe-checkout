<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\StripeService;

class StripeWebhookController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Handle Stripe webhook events
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        // Verify webhook signature
        if (!$this->stripeService->verifyWebhook($payload, $sigHeader)) {
            Log::error('Stripe webhook signature verification failed', [
                'signature_header' => $sigHeader,
                'payload_preview' => substr($payload, 0, 100)
            ]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);
        if (!$event || !isset($event['type'])) {
            Log::error('Invalid Stripe webhook payload');
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        // Handle Stripe events
        switch ($event['type']) {
            case 'payment_intent.succeeded':
                $this->stripeService->handlePaymentSucceeded($event);
                break;
            case 'payment_intent.payment_failed':
                $this->stripeService->handlePaymentFailed($event);
                break;
            case 'payment_intent.canceled':
                $this->stripeService->handlePaymentCanceled($event);
                break;
            case 'payment_intent.created':
                // PaymentIntent created - just log, no action needed
                Log::info('Stripe PaymentIntent created: ' . ($event['data']['object']['id'] ?? 'unknown'));
                break;
            default:
                Log::info('Unhandled Stripe event type: ' . $event['type']);
        }

        return response()->json(['status' => 'success']);
    }
}
