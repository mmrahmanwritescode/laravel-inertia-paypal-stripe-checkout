<?php

use App\Models\CartItem;
use Illuminate\Support\Facades\Session;

if (!function_exists('getSessionId')) {
    function getSessionId(): string
    {
        if (!Session::has('cart_session_id')) {
            Session::put('cart_session_id', Session::getId());
        }
        return Session::get('cart_session_id');
    }
}

if (!function_exists('top_cart_query')) {
    function top_cart_query()
    {
        return CartItem::with('foodItem')
            ->where('session_id', getSessionId())
            ->get();
    }
}

if (!function_exists('cart_summary')) {
    function cart_summary(): array
    {
        $cartItems = top_cart_query();
        $total = $cartItems->sum('subtotal');
        $count = $cartItems->sum('quantity');
        
        return [
            'total' => $total,
            'count' => $count,
            'items' => $cartItems->count()
        ];
    }
}

if (!function_exists('cart_count')) {
    function cart_count(): int
    {
        return CartItem::where('session_id', getSessionId())->sum('quantity');
    }
}

if (!function_exists('clearCart')) {
    function clearCart(): void
    {
        CartItem::where('session_id', getSessionId())->delete();
    }
}

if (!function_exists('getLastOrderNo')) {
    function getLastOrderNo(): string
    {
        return 'ORD' . now()->format('YmdHis') . rand(100, 999);
    }
}
