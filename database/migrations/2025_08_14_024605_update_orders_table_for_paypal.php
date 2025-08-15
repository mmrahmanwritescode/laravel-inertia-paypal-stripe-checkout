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
        Schema::table('orders', function (Blueprint $table) {
            // Update payment method enum to use PayPal instead of Stripe
            $table->enum('payment_method', ['paypal', 'N/A'])->default('paypal')->change();
            
            // Rename payment_intent_id to paypal_order_id
            $table->renameColumn('payment_intent_id', 'paypal_order_id');
            
            // Add PayPal specific columns
            $table->string('paypal_capture_id')->nullable()->after('paypal_order_id');
            $table->string('paypal_refund_id')->nullable()->after('paypal_capture_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Revert payment method enum back to Stripe
            $table->enum('payment_method', ['stripe', 'N/A'])->default('stripe')->change();
            
            // Rename back to payment_intent_id
            $table->renameColumn('paypal_order_id', 'payment_intent_id');
            
            // Drop PayPal specific columns
            $table->dropColumn(['paypal_capture_id', 'paypal_refund_id']);
        });
    }
};
