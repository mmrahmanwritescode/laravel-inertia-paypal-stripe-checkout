<template>
    <AppLayout>
        <div>
            <h1 class="h2 fw-bold text-dark mb-4">Shopping Cart</h1>
            
            <div class="row g-4">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div v-if="cartData.items.length === 0" class="text-center py-5">
                        <p class="text-muted fs-5">Your cart is empty</p>
                        
                        <!-- Show random food items when cart is empty -->
                        <div v-if="randomFoodItems.length > 0" class="mt-4">
                            <h5 class="text-muted mb-3">Try these items:</h5>
                            <div class="row g-3 mb-4">
                                <div v-for="item in randomFoodItems" :key="item.id" class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">{{ item.name }}</h6>
                                            <p class="card-text small text-muted">{{ item.description }}</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold text-primary">${{ item.price }}</span>
                                                <button 
                                                    @click="addToCart(item.id)"
                                                    class="btn btn-sm btn-outline-primary"
                                                >
                                                    Add to Cart
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button @click="addSampleItems" class="btn btn-primary btn-lg mt-3">
                            Add 3 Random Items
                        </button>
                    </div>
                    
                    <div v-else class="d-grid gap-3">
                        <CartItem 
                            v-for="item in cartData.items" 
                            :key="item.id" 
                            :item="item"
                            @updated="handleCartUpdate"
                        />
                        
                        <div class="d-flex justify-content-between pt-3 border-top">
                            <button 
                                @click="clearCart"
                                class="btn btn-outline-danger"
                            >
                                Clear Cart
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="col-lg-4">
                    <!-- Show random items suggestion when cart has items -->
                    <div v-if="cartData.items.length > 0 && randomFoodItems.length > 0" class="card mb-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">You might also like</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <div v-for="item in randomFoodItems" :key="'suggestion-' + item.id" class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="fw-medium small">{{ item.name }}</div>
                                        <div class="text-primary small">${{ item.price }}</div>
                                    </div>
                                    <button 
                                        @click="addToCart(item.id)"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <CartSummary 
                        :summary="cartData.summary"
                        @checkout="goToCheckout"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import CartItem from '@/Components/Cart/CartItem.vue'
import CartSummary from '@/Components/Cart/CartSummary.vue'

defineProps({
    cartData: Object,
    randomFoodItems: Array
})

const handleCartUpdate = () => {
    router.reload()
}

const clearCart = () => {
    router.post('/clear-cart', {}, {
        onSuccess: () => router.reload()
    })
}

const addSampleItems = () => {
    router.post('/cart/add-samples', {}, {
        onSuccess: () => router.reload()
    })
}

const addToCart = (foodItemId) => {
    router.post('/cart/add-item', {
        food_item_id: foodItemId,
        quantity: 1
    }, {
        onSuccess: () => router.reload()
    })
}

const goToCheckout = () => {
    router.visit('/checkout')
}
</script>
