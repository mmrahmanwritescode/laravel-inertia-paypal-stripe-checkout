<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    protected $paypalService;
    protected $stripeService;

    public function __construct(PayPalService $paypalService, \App\Services\StripeService $stripeService)
    {
        $this->paypalService = $paypalService;
        $this->stripeService = $stripeService;
    }
    /**
     * Create Stripe Payment Intent and return client secret
     */
    public function createStripeOrder(Request $request)
    {
        $request->validate([
            'total_price' => 'required|numeric|min:0.50'
        ]);

        $response = $this->stripeService->createPaymentIntent($request->total_price);

        if (isset($response['error'])) {
            return response()->json(['error' => $response['error']], 500);
        }

        return response()->json($response);
    }

    /**
     * Handle Stripe payment status after checkout
     */
    public function handleStripePaymentStatus(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
            'order_id' => 'required|exists:orders,id'
        ]);

        try {
            $order = Order::findOrFail($request->order_id);
            // In real implementation, verify payment intent status with Stripe API
            $order->update([
                'transaction_id' => $request->payment_intent_id,
                'payment_method' => 'stripe',
                'status' => 'order_placed',
                'payment_intent_id' => $request->payment_intent_id
            ]);
            clearCart();

            return response()->json([
                'success' => true,
                'transactionID' => $request->payment_intent_id,
                'redirect_url' => route('orders.confirm', $order->purchase_order_id)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create Stripe order and save to database
     */
    public function createStripeOrderAndSave(Request $request)
    {
        $validationRules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'shipping_cost' => 'required|numeric|min:0',
            'order_type' => 'required|in:delivery,takeaway,pay_on_spot',
            'payment_method' => 'required|string',
            'payment_intent_id' => 'nullable|string'
        ];

        // Add address validation for delivery orders
        if ($request->order_type === 'delivery') {
            $validationRules['address'] = 'required|string|max:500';
            $validationRules['post_code'] = 'required|string|max:20';
        } else {
            $validationRules['address'] = 'nullable|string|max:500';
            $validationRules['post_code'] = 'nullable|string|max:20';
        }

        $request->validate($validationRules);

        try {
            DB::beginTransaction();

            $order = $this->saveTempOrder($request);
            $order->update([
                'status' => 'order_in_progress',
                'payment_intent_id' => $request->payment_intent_id
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'purchase_order_id' => $order->purchase_order_id,
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createPayPalOrder(Request $request)
    {
        $request->validate([
            'total_price' => 'required|numeric|min:0.50'
        ]);

        $response = $this->paypalService->createOrder($request->total_price);

        if ($response['error']) {
            return response()->json(['error' => $response['error']], 500);
        }

        return response()->json($response['api']);
    }

    public function createCustomerAndOrder(Request $request)
    {
        $validationRules = [
            'paypal_order_id' => 'required|string',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'shipping_cost' => 'required|numeric|min:0',
            'order_type' => 'required|in:delivery,takeaway,pay_on_spot'
        ];

        // Add address validation for delivery orders
        if ($request->order_type === 'delivery') {
            $validationRules['address'] = 'required|string|max:500';
            $validationRules['post_code'] = 'required|string|max:20';
        } else {
            $validationRules['address'] = 'nullable|string|max:500';
            $validationRules['post_code'] = 'nullable|string|max:20';
        }

        $request->validate($validationRules);

        try {
            DB::beginTransaction();

            // Create customer with PayPal
            $fullName = $request->first_name . ' ' . $request->last_name;
            $customerResponse = $this->paypalService->createCustomer(
                $request->paypal_order_id,
                $request->email,
                $fullName
            );

            if ($customerResponse['error']) {
                throw new \Exception($customerResponse['error']);
            }

            // Save order
            $order = $this->saveTempOrder($request);
            $order->update(['paypal_order_id' => $request->paypal_order_id]);

            DB::commit();

            return response()->json([
                'customer_id' => $customerResponse['api']['customer_id'],
                'order_id' => $order->id,
                'purchase_order_id' => $order->purchase_order_id,
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function handlePayPalPaymentStatus(Request $request)
    {
        $request->validate([
            'paypal_order_id' => 'required|string',
            'order_id' => 'required|exists:orders,id'
        ]);

        try {
            $order = Order::findOrFail($request->order_id);
            
            // Capture the PayPal payment
            $response = $this->paypalService->captureOrder($request->paypal_order_id);

            if ($response['paymentStatus']) {
                $order->update([
                    'transaction_id' => $response['transactionID'],
                    'paypal_capture_id' => $response['captureID'] ?? null,
                    'status' => 'order_placed'
                ]);
                clearCart();

                return response()->json([
                    'success' => true,
                    'transactionID' => $response['transactionID'],
                    'redirect_url' => route('orders.confirm', $order->purchase_order_id)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $response['error'] ?: 'Payment capture failed'
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(): Response
    {
        $cartItems = top_cart_query();
        $cartSummary = cart_summary();

        $stripePublishableKey = config('services.stripe.publishable_key');
        if (empty($cartItems) || $cartSummary['count'] === 0) {
            return Inertia::render('Checkout/Show', [
                'cartData' => [
                    'items' => [],
                    'summary' => ['total' => 0, 'count' => 0]
                ],
                'paypal' => [
                    'client_id' => config('services.paypal.client_id')
                ],
                'stripe' => [
                    'publishable_key' => $stripePublishableKey
                ]
            ]);
        }

        return Inertia::render('Checkout/Show', [
            'cartData' => [
                'items' => $cartItems,
                'summary' => $cartSummary
            ],
            'paypal' => [
                'client_id' => config('services.paypal.client_id')
            ],
            'stripe' => [
                'publishable_key' => $stripePublishableKey
            ]
        ]);
    }    

    public function store(Request $request)
    {
        $validationRules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'shipping_cost' => 'required|numeric|min:0',
            'order_type' => 'required|in:delivery,takeaway,pay_on_spot'
        ];

        // Add address validation for delivery orders
        if ($request->order_type === 'delivery') {
            $validationRules['address'] = 'required|string|max:500';
            $validationRules['post_code'] = 'required|string|max:20';
        } else {
            $validationRules['address'] = 'nullable|string|max:500';
            $validationRules['post_code'] = 'nullable|string|max:20';
        }

        $request->validate($validationRules);

        try {
            DB::beginTransaction();

            $order = $this->saveTempOrder($request);
            $order->update([
                'status' => 'order_placed',
                'transaction_id' => 'PAY_ON_SPOT'
            ]);

            clearCart();
            DB::commit();

            return redirect()->route('orders.confirm', $order->purchase_order_id)
                ->with('success', 'Order placed successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withErrors(['error' => 'Failed to place order: ' . $e->getMessage()]);
        }
    }    

    protected function saveTempOrder(Request $request): Order
    {
        // Create or update user
        $user = User::firstOrCreate(
            ['email' => $request->email],
            [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'address' => $request->address,
                'post_code' => $request->post_code,
            ]
        );

        // Create order
        $cartSummary = cart_summary();
        $paymentMethod = 'N/A';
        if ($request->order_type !== 'pay_on_spot') {
            $paymentMethod = $request->payment_method ?? 'paypal';
        }
        $order = Order::create([
            'user_id' => $user->id,
            'purchase_order_id' => getLastOrderNo(),
            'status' => 'order_in_progress',
            'payment_method' => $paymentMethod,
            'price' => $cartSummary['total'],
            'shipping_cost' => $request->shipping_cost,
            'transaction_id' => 'PENDING',
            'notes' => $request->notes,
            'order_type' => $request->order_type
        ]);

        // Create order items
        foreach (top_cart_query() as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'food_item_id' => $item->food_item_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'discount' => $item->discount
            ]);
        }

        return $order;
    }
}
