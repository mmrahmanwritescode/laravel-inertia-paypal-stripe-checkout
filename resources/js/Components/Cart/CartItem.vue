<template>
    <div class="card">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-auto">
                    <img 
                        :src="item.food_item?.image || '/empty.png'" 
                        :alt="item.food_item?.name"
                        class="cart-item-img rounded"
                    />
                </div>
                
                <div class="col">
                    <h5 class="card-title mb-1">{{ item.food_item?.name }}</h5>
                    <p class="card-text text-muted small mb-1">{{ item.food_item?.description }}</p>
                    <p class="text-success fw-bold mb-0">${{ item.price }}</p>
                </div>
                
                <div class="col-auto">
                    <div class="btn-group" role="group">
                        <button 
                            @click="updateQuantity(item.quantity - 1)"
                            :disabled="item.quantity <= 1"
                            class="btn btn-outline-secondary btn-sm"
                        >
                            -
                        </button>
                        
                        <span class="btn btn-outline-secondary btn-sm disabled">{{ item.quantity }}</span>
                        
                        <button 
                            @click="updateQuantity(item.quantity + 1)"
                            class="btn btn-outline-secondary btn-sm"
                        >
                            +
                        </button>
                    </div>
                </div>
                
                <div class="col-auto text-end">
                    <p class="fw-bold mb-1">${{ subtotal }}</p>
                    <button 
                        @click="removeItem"
                        class="btn btn-link text-danger p-0 small"
                    >
                        Remove
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    item: Object
})

const emit = defineEmits(['updated'])

const subtotal = computed(() => {
    return ((props.item.price - props.item.discount) * props.item.quantity).toFixed(2)
})

const updateQuantity = (newQuantity) => {
    if (newQuantity < 1) return
    
    router.post('/cart/update', {
        item_id: props.item.id,
        quantity: newQuantity
    }, {
        onSuccess: () => emit('updated')
    })
}

const removeItem = () => {
    router.delete(`/cart/remove/${props.item.id}`, {
        onSuccess: () => emit('updated')
    })
}
</script>
