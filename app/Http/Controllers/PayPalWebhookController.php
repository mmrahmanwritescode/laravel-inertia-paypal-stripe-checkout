<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Services\PayPalService;

class PayPalWebhookController extends Controller
{
    protected $paypalService;
    
    public function __construct(PayPalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }
    
    /**
     * Handle PayPal webhook events
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $headers = $request->headers->all();

        try {
            // Verify webhook signature (implement proper verification in production)
            if (!$this->paypalService->verifyWebhook($payload, $headers)) {
                Log::error('PayPal webhook signature verification failed');
                return response('Invalid signature', 400);
            }

            $data = json_decode($payload, true);
            
            if (!$data) {
                Log::error('Invalid PayPal webhook payload');
                return response('Invalid payload', 400);
            }

            Log::info('PayPal webhook received', [
                'event_type' => $data['event_type'] ?? 'unknown',
                'resource_type' => $data['resource_type'] ?? 'unknown'
            ]);

            // Handle the event
            switch ($data['event_type'] ?? '') {
                case 'CHECKOUT.ORDER.APPROVED':
                    $this->handleOrderApproved($data);
                    break;
                    
                case 'PAYMENT.CAPTURE.COMPLETED':
                    $this->handlePaymentCaptured($data);
                    break;
                    
                case 'PAYMENT.CAPTURE.DENIED':
                    $this->handlePaymentDenied($data);
                    break;
                    
                case 'PAYMENT.CAPTURE.REFUNDED':
                    $this->handlePaymentRefunded($data);
                    break;
                    
                default:
                    Log::info('Unhandled PayPal webhook event', ['event_type' => $data['event_type'] ?? 'unknown']);
                    break;
            }

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('PayPal webhook processing error', ['error' => $e->getMessage()]);
            return response('Internal Server Error', 500);
        }
    }

    /**
     * Handle order approved event
     */
    private function handleOrderApproved($data)
    {
        try {
            $orderId = $data['resource']['id'] ?? null;
            
            if (!$orderId) {
                Log::warning('PayPal order approved webhook missing order ID');
                return;
            }

            $order = Order::where('paypal_order_id', $orderId)->first();

            if ($order) {
                Log::info('PayPal order approved', [
                    'paypal_order_id' => $orderId,
                    'order_id' => $order->id
                ]);
                
                // Update order status if needed
                if ($order->status === 'order_in_progress') {
                    $order->update(['status' => 'order_placed']);
                }
            } else {
                Log::warning('PayPal order approved but no matching order found', ['paypal_order_id' => $orderId]);
            }

        } catch (\Exception $e) {
            Log::error('Error handling PayPal order approved', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle payment captured event
     */
    private function handlePaymentCaptured($data)
    {
        try {
            $captureId = $data['resource']['id'] ?? null;
            $orderId = $data['resource']['supplementary_data']['related_ids']['order_id'] ?? null;
            
            if (!$captureId || !$orderId) {
                Log::warning('PayPal payment captured webhook missing required data');
                return;
            }

            $order = Order::where('paypal_order_id', $orderId)->first();

            if ($order) {
                $order->update([
                    'paypal_capture_id' => $captureId,
                    'status' => 'confirmed'
                ]);

                Log::info('PayPal payment captured and order updated', [
                    'paypal_order_id' => $orderId,
                    'capture_id' => $captureId,
                    'order_id' => $order->id
                ]);
            } else {
                Log::warning('PayPal payment captured but no matching order found', [
                    'paypal_order_id' => $orderId,
                    'capture_id' => $captureId
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error handling PayPal payment captured', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle payment denied event
     */
    private function handlePaymentDenied($data)
    {
        try {
            $orderId = $data['resource']['supplementary_data']['related_ids']['order_id'] ?? null;
            
            if (!$orderId) {
                Log::warning('PayPal payment denied webhook missing order ID');
                return;
            }

            $order = Order::where('paypal_order_id', $orderId)->first();

            if ($order) {
                $order->update(['status' => 'payment_failed']);

                Log::info('PayPal payment denied and order updated', [
                    'paypal_order_id' => $orderId,
                    'order_id' => $order->id
                ]);
            } else {
                Log::warning('PayPal payment denied but no matching order found', ['paypal_order_id' => $orderId]);
            }

        } catch (\Exception $e) {
            Log::error('Error handling PayPal payment denied', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle payment refunded event
     */
    private function handlePaymentRefunded($data)
    {
        try {
            $refundId = $data['resource']['id'] ?? null;
            $captureId = $data['resource']['links'][0]['href'] ?? null;
            
            // Extract capture ID from the link if available
            if ($captureId && preg_match('/captures\/([^\/]+)/', $captureId, $matches)) {
                $captureId = $matches[1];
            }

            if (!$refundId) {
                Log::warning('PayPal payment refunded webhook missing refund ID');
                return;
            }

            $order = Order::where('paypal_capture_id', $captureId)->first();

            if ($order) {
                $order->update([
                    'status' => 'refunded',
                    'paypal_refund_id' => $refundId
                ]);

                Log::info('PayPal payment refunded and order updated', [
                    'capture_id' => $captureId,
                    'refund_id' => $refundId,
                    'order_id' => $order->id
                ]);
            } else {
                Log::warning('PayPal payment refunded but no matching order found', [
                    'capture_id' => $captureId,
                    'refund_id' => $refundId
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error handling PayPal payment refunded', ['error' => $e->getMessage()]);
        }
    }
}
