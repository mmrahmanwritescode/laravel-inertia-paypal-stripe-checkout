<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $orders = Order::with(['orderItems.foodItem','user'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                // Ensure total is calculated
                $order->total = $order->total;
                return $order;
            }); 
            
        // Debug: let's see what the first order looks like
        // if ($orders->isNotEmpty()) {
        //     Log::info('Order structure:', $orders->first()->toArray());
        // }

        return Inertia::render('Orders/Index', [
            'orders' => $orders
        ]);
    }

    public function confirm($purchaseOrderId): Response
    {
        if (empty($purchaseOrderId)) {
            abort(404);
        }
        clearCart();
        $order = Order::with(['orderItems.foodItem', 'user'])
            ->where('purchase_order_id', $purchaseOrderId)
            ->firstOrFail();

        return Inertia::render('Orders/Confirmed', [
            'order' => $order
        ]);
    }

    public function updateStatus(Request $request, $purchaseOrderId)
    {
        $request->validate([
            'status' => 'required|in:confirmed,cancelled,completed',
            'reason' => 'nullable|string|max:255',
            'reason_text' => 'nullable|string|max:500'
        ]);

        $order = Order::where('purchase_order_id', $purchaseOrderId)->firstOrFail();

        // Check if status transition is allowed
        $allowedTransitions = [
            'order_in_progress' => ['confirmed','cancelled'],
            'confirmed' => ['cancelled'],
            'order_placed' => ['confirmed', 'cancelled']
        ];

        if (!isset($allowedTransitions[$order->status]) || 
            !in_array($request->status, $allowedTransitions[$order->status])) {
            return back()->withErrors([
                'message' => 'Invalid status transition from ' . $order->status . ' to ' . $request->status
            ]);
        }

        // Handle cancellation logic
        if ($request->status === 'cancelled') {
            $this->handleOrderCancellation($order, $request->reason, $request->reason_text);
        }

        // Update order status
        $order->update([
            'status' => $request->status
        ]);

        // Log status change
        Log::info('Order status updated', [
            'order_id' => $order->id,
            'purchase_order_id' => $order->purchase_order_id,
            'old_status' => $order->getOriginal('status'),
            'new_status' => $request->status,
            'reason' => $request->reason,
            'reason_text' => $request->reason_text
        ]);

        return redirect()->back()->with('success', 'Order status updated successfully.');
    }

    private function handleOrderCancellation($order, $reason = null, $reasonText = null)
    {
        // Log cancellation details
        Log::info('Order cancellation initiated', [
            'order_id' => $order->id,
            'purchase_order_id' => $order->purchase_order_id,
            'payment_method' => $order->payment_method,
            'transaction_id' => $order->transaction_id,
            'reason' => $reason,
            'reason_text' => $reasonText
        ]);

        // Here you could add additional cancellation logic:
        // - Initiate refund process for Stripe payments
        // - Send cancellation emails
        // - Update inventory
        // - Notify kitchen/delivery team

        // For now, just log the cancellation
        if ($order->payment_method === 'stripe' && $order->transaction_id !== 'PENDING') {
            Log::info('Stripe refund should be initiated for order', [
                'order_id' => $order->id,
                'transaction_id' => $order->transaction_id
            ]);
            // TODO: Implement Stripe refund logic here
        }
    }
}
