<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PayPalService
{
    protected $baseUrl;
    protected $clientId;
    protected $clientSecret;
    protected $accessToken;
    
    public function __construct()
    {
        $mode = Config::get('paypal.mode', 'sandbox');
        $this->clientId = Config::get('paypal.client_id');
        $this->clientSecret = Config::get('paypal.client_secret');
        
        $this->baseUrl = $mode === 'sandbox' 
            ? 'https://api.sandbox.paypal.com' 
            : 'https://api.paypal.com';
    }
    
    /**
     * Get PayPal access token
     */
    protected function getAccessToken(): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }
        
        try {
            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()
                ->post($this->baseUrl . '/v1/oauth2/token', [
                    'grant_type' => 'client_credentials'
                ]);
                
            if ($response->successful()) {
                $data = $response->json();
                $this->accessToken = $data['access_token'];
                return $this->accessToken;
            }
            
            Log::error('PayPal Access Token Failed', ['response' => $response->body()]);
            return null;
            
        } catch (\Exception $e) {
            Log::error('PayPal Access Token Exception', ['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * Create PayPal order (equivalent to Stripe Payment Intent)
     */
    public function createOrder($totalPrice, $currency = 'USD', $description = null): array
    {
        $response = ['api' => '', 'error' => ''];
        
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                throw new \Exception('Failed to get PayPal access token');
            }
            
            $orderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => number_format($totalPrice, 2, '.', '')
                    ],
                    'description' => $description ?: 'Order Payment #' . now()->format('YmdHis')
                ]],
                'application_context' => [
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW',
                    'return_url' => url('/checkout/paypal/success'),
                    'cancel_url' => url('/checkout/paypal/cancel')
                ]
            ];
            
            $httpResponse = Http::withToken($accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Prefer' => 'return=representation'
                ])
                ->post($this->baseUrl . '/v2/checkout/orders', $orderData);
            
            if ($httpResponse->successful()) {
                $orderResult = $httpResponse->json();
                
                $response['api'] = [
                    'id' => $orderResult['id'],
                    'status' => $orderResult['status'],
                    'links' => $orderResult['links'] ?? []
                ];
                
                Log::info('PayPal Order Created', ['order_id' => $orderResult['id']]);
            } else {
                throw new \Exception('PayPal API Error: ' . $httpResponse->body());
            }
            
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
            Log::error('PayPal Order Creation Failed', ['error' => $e->getMessage()]);
        }
        
        return $response;
    }
    
    /**
     * Create customer and associate with order (PayPal doesn't require explicit customer creation)
     */
    public function createCustomer($orderId, $email, $name): array
    {
        $response = ['api' => '', 'error' => ''];
        
        try {
            // For PayPal, we don't need to create a separate customer
            // Customer information is captured during payment approval
            $response['api'] = [
                'id' => $orderId,
                'customer_id' => null, // PayPal handles customer data internally
                'email' => $email,
                'name' => $name
            ];
            
            Log::info('PayPal Customer Data Associated', ['order_id' => $orderId, 'email' => $email]);
            
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
            Log::error('PayPal Customer Association Failed', ['error' => $e->getMessage()]);
        }
        
        return $response;
    }
    
    /**
     * Capture PayPal order payment
     */
    public function captureOrder($orderId): array
    {
        $response = ['paymentStatus' => false, 'error' => '', 'transactionID' => ''];
        
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                throw new \Exception('Failed to get PayPal access token');
            }
            
            $httpResponse = Http::withToken($accessToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Prefer' => 'return=representation'
                ])
                ->post($this->baseUrl . "/v2/checkout/orders/{$orderId}/capture");
            
            if ($httpResponse->successful()) {
                $result = $httpResponse->json();
                
                if ($result['status'] === 'COMPLETED') {
                    $captureId = $result['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;
                    
                    $response['paymentStatus'] = true;
                    $response['transactionID'] = $orderId;
                    $response['captureID'] = $captureId;
                    
                    Log::info('PayPal Payment Captured', [
                        'order_id' => $orderId, 
                        'capture_id' => $captureId
                    ]);
                } else {
                    $response['error'] = 'Payment capture failed. Status: ' . $result['status'];
                    Log::warning('PayPal Payment Capture Failed', ['order_id' => $orderId, 'status' => $result['status']]);
                }
            } else {
                throw new \Exception('PayPal API Error: ' . $httpResponse->body());
            }
            
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
            Log::error('PayPal Payment Capture Error', ['order_id' => $orderId, 'error' => $e->getMessage()]);
        }
        
        return $response;
    }
    
    /**
     * Get PayPal order details
     */
    public function getOrder($orderId): array
    {
        $response = ['order' => null, 'error' => ''];
        
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                throw new \Exception('Failed to get PayPal access token');
            }
            
            $httpResponse = Http::withToken($accessToken)
                ->get($this->baseUrl . "/v2/checkout/orders/{$orderId}");
                
            if ($httpResponse->successful()) {
                $response['order'] = $httpResponse->json();
                Log::info('PayPal Order Retrieved', ['order_id' => $orderId]);
            } else {
                throw new \Exception('PayPal API Error: ' . $httpResponse->body());
            }
            
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
            Log::error('PayPal Order Retrieval Failed', ['order_id' => $orderId, 'error' => $e->getMessage()]);
        }
        
        return $response;
    }
    
    /**
     * Refund PayPal payment
     */
    public function refund($captureId, $amount = null, $currency = 'USD'): array
    {
        $response = [
            'refundStatus' => false,
            'refundID' => '',
            'api_error' => ''
        ];
        
        try {
            $accessToken = $this->getAccessToken();
            if (!$accessToken) {
                throw new \Exception('Failed to get PayPal access token');
            }
            
            $refundData = [];
            if ($amount) {
                $refundData['amount'] = [
                    'value' => number_format($amount, 2, '.', ''),
                    'currency_code' => $currency
                ];
            }
            
            $httpResponse = Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->baseUrl . "/v2/payments/captures/{$captureId}/refund", $refundData);
            
            if ($httpResponse->successful()) {
                $result = $httpResponse->json();
                
                if ($result['status'] === 'COMPLETED') {
                    $response['refundStatus'] = true;
                    $response['refundID'] = $result['id'];
                    
                    Log::info('PayPal Refund Processed', [
                        'capture_id' => $captureId,
                        'refund_id' => $result['id'],
                        'amount' => $amount
                    ]);
                } else {
                    $response['api_error'] = 'Refund failed. Status: ' . $result['status'];
                    Log::warning('PayPal Refund Failed', ['capture_id' => $captureId, 'status' => $result['status']]);
                }
            } else {
                throw new \Exception('PayPal API Error: ' . $httpResponse->body());
            }
            
        } catch (\Exception $e) {
            $response['api_error'] = $e->getMessage();
            Log::error('PayPal Refund Error', ['capture_id' => $captureId, 'error' => $e->getMessage()]);
        }
        
        return $response;
    }
    
    /**
     * Verify PayPal webhook signature
     */
    public function verifyWebhook($requestBody, $headers): bool
    {
        try {
            $webhookId = Config::get('paypal.webhook_id');
            
            if (!$webhookId) {
                Log::warning('PayPal webhook ID not configured');
                return false;
            }
            
            // PayPal webhook verification implementation
            // This would require proper webhook signature verification
            // For now, return true but implement proper verification in production
            
            Log::info('PayPal Webhook Verified (placeholder implementation)');
            return true;
            
        } catch (\Exception $e) {
            Log::error('PayPal Webhook Verification Failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
