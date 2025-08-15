<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\FoodItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function index(): Response
    {
        $cartItems = top_cart_query();
        // Get 3 random food items to display as suggestions
        $randomFoodItems = FoodItem::where('is_active', true)
            ->inRandomOrder()
            ->limit(3)
            ->get();

        return Inertia::render('Cart/Index', [
            'cartData' => [
                'items' => $cartItems,
                'summary' => cart_summary()
            ],
            'randomFoodItems' => $randomFoodItems
        ]);
    }
    
    public function addSamples()
    {
        // Get 3 random food items from the database
        $randomFoodItems = FoodItem::where('is_active', true)
            ->inRandomOrder()
            ->limit(3)
            ->get();
        
        // If no food items exist, create some sample ones
        if ($randomFoodItems->isEmpty()) {
            $sampleItems = [
                ['name' => 'Margherita Pizza', 'price' => 12.99, 'description' => 'Classic pizza with tomato and mozzarella', 'is_active' => true],
                ['name' => 'Chicken Burger', 'price' => 9.99, 'description' => 'Grilled chicken with lettuce and tomato', 'is_active' => true],
                ['name' => 'Caesar Salad', 'price' => 7.99, 'description' => 'Fresh romaine with caesar dressing', 'is_active' => true],
                ['name' => 'Fish & Chips', 'price' => 14.99, 'description' => 'Beer battered fish with crispy chips', 'is_active' => true],
                ['name' => 'Beef Tacos', 'price' => 11.99, 'description' => 'Soft tacos with seasoned beef and salsa', 'is_active' => true],
                ['name' => 'Chicken Wings', 'price' => 8.99, 'description' => 'Spicy buffalo wings with blue cheese', 'is_active' => true],
            ];
            
            foreach ($sampleItems as $itemData) {
                FoodItem::firstOrCreate(
                    ['name' => $itemData['name']],
                    $itemData
                );
            }
            
            // Get the random items again after creating them
            $randomFoodItems = FoodItem::where('is_active', true)
                ->inRandomOrder()
                ->limit(3)
                ->get();
        }
        
        // Add the random food items to cart
        foreach ($randomFoodItems as $foodItem) {
            CartItem::updateOrCreate(
                [
                    'session_id' => getSessionId(),
                    'food_item_id' => $foodItem->id
                ],
                [
                    'quantity' => 1,
                    'price' => $foodItem->price,
                    'discount' => 0
                ]
            );
        }
        
        return redirect()->back()->with('success', 'Random items added to cart!');
    }
    
    public function addItem(Request $request)
    {
        $request->validate([
            'food_item_id' => 'required|exists:food_items,id',
            'quantity' => 'integer|min:1'
        ]);
        
        $foodItem = FoodItem::findOrFail($request->food_item_id);
        $quantity = $request->quantity ?? 1;
        
        $cartItem = CartItem::where('session_id', getSessionId())
            ->where('food_item_id', $foodItem->id)
            ->first();
            
        if ($cartItem) {
            // Item exists, increment quantity
            $cartItem->update(['quantity' => $cartItem->quantity + $quantity]);
        } else {
            // Create new cart item
            CartItem::create([
                'session_id' => getSessionId(),
                'food_item_id' => $foodItem->id,
                'quantity' => $quantity,
                'price' => $foodItem->price,
                'discount' => 0
            ]);
        }
        
        return redirect()->back()->with('success', $foodItem->name . ' added to cart!');
    }
    
    public function updateQuantity(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:1'
        ]);
        
        $cartItem = CartItem::where('id', $request->item_id)
            ->where('session_id', getSessionId())
            ->first();
            
        if ($cartItem) {
            $cartItem->update(['quantity' => $request->quantity]);
        }
        
        return redirect()->back();
    }
    
    public function removeItem($itemId)
    {
        CartItem::where('id', $itemId)
            ->where('session_id', getSessionId())
            ->delete();
            
        return redirect()->back()->with('success', 'Item removed from cart');
    }
    
    public function clearCart()
    {
        clearCart();
        return redirect()->back()->with('success', 'Cart cleared successfully');
    }
}
