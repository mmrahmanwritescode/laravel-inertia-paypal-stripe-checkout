<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'purchase_order_id',
        'status',
        'payment_method',
        'price',
        'shipping_cost',
        'transaction_id',
        'paypal_order_id',
        'paypal_capture_id',
        'paypal_refund_id',
        'notes',
        'order_type',
        'payment_intent_id', // Stripe
        'paypal_order_id', // PayPal
        'paypal_capture_id', // PayPal
        'paypal_refund_id', // PayPal
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'post_code'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
    ];

    protected $appends = ['total'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getTotalAttribute(): float
    {
        $price = $this->price ?? 0;
        $shippingCost = $this->shipping_cost ?? 0;
        return $price + $shippingCost;
    }
}
