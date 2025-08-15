<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('purchase_order_id')->unique();
            $table->enum('status', ['order_in_progress', 'order_placed', 'confirmed', 'cancelled'])->default('order_in_progress');
            $table->enum('payment_method', ['paypal', 'stripe', 'N/A'])->default('N/A');
            $table->decimal('price', 8, 2);
            $table->decimal('shipping_cost', 8, 2)->default(0);
            $table->string('transaction_id')->nullable();
            $table->string('payment_intent_id')->nullable(); // Stripe
            $table->string('paypal_order_id')->nullable(); // PayPal
            $table->string('paypal_capture_id')->nullable(); // PayPal
            $table->string('paypal_refund_id')->nullable(); // PayPal
            $table->text('notes')->nullable();
            $table->enum('order_type', ['delivery', 'takeaway', 'pay_on_spot'])->default('delivery');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
