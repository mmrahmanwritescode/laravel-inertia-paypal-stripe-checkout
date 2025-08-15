<template>
    <div class="card sticky-top">
        <div class="card-header">
            <h5 class="card-title mb-0">Order Summary</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Items ({{ summary.count }})</span>
                <span>${{ summary.total.toFixed(2) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Shipping</span>
                <span>$5.00</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between fw-bold">
                <span>Total</span>
                <span>${{ (summary.total + 5).toFixed(2) }}</span>
            </div>
        </div>
        <div class="card-footer">
            <button 
                @click="$emit('checkout')"
                :disabled="summary.count === 0"
                class="btn btn-primary w-100"
            >
                Proceed to Checkout
            </button>
        </div>
    </div>
</template>

<script setup>
defineProps({
    summary: Object
})

defineEmits(['checkout'])
</script>
