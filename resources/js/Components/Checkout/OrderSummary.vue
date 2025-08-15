<template>
    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">Order Summary</h4>
        </div>
        <div class="card-body">
            <div class="list-group list-group-flush">
                <!-- Order Items -->
                <div v-for="item in cartData.items" :key="item.id" class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <div class="flex-grow-1">
                        <div class="fw-medium">{{ item.food_item?.name }}</div>
                        <div class="text-muted small">Qty: {{ item.quantity }} × ${{ item.price }}</div>
                    </div>
                    <div class="text-end">
                        <div class="fw-medium">${{ ((item.price - item.discount) * item.quantity).toFixed(2) }}</div>
                    </div>
                </div>
            </div>
            
            <div class="border-top pt-3 mt-3">
                <div class="row mb-2">
                    <div class="col">
                        <span class="text-muted">Subtotal</span>
                    </div>
                    <div class="col-auto">
                        <span>${{ cartData.summary.total.toFixed(2) }}</span>
                    </div>
                </div>
                
                <div class="row mb-2">
                    <div class="col">
                        <span class="text-muted">Shipping</span>
                    </div>
                    <div class="col-auto">
                        <span v-if="shippingCost > 0">${{ shippingCost.toFixed(2) }}</span>
                        <span v-else class="text-success">Free</span>
                    </div>
                </div>
                
                <div class="row border-top pt-2">
                    <div class="col">
                        <span class="fw-bold fs-5">Total</span>
                    </div>
                    <div class="col-auto">
                        <span class="fw-bold fs-5">${{ (cartData.summary.total + shippingCost).toFixed(2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    cartData: Object,
    shippingCost: {
        type: Number,
        default: 0
    }
})
</script>
