<template>
    <AppLayout>
        <div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h2 fw-bold text-dark">Order History</h1>
                <Link href="/" class="btn btn-primary">
                    Continue Shopping
                </Link>
            </div>
            
            <div v-if="orders.length === 0" class="text-center py-5">
                <div class="mb-4">
                    <i class="bi bi-receipt-cutoff" style="font-size: 4rem; color: #6c757d;"></i>
                </div>
                <h3 class="h4 text-muted">No orders found</h3>
                <p class="text-muted">You haven't placed any orders yet.</p>
                <Link href="/" class="btn btn-primary mt-3">
                    Start Shopping
                </Link>
            </div>
            
            <div v-else class="row g-4">
                <div v-for="order in orders" :key="order.id" class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <h6 class="mb-0">Order #{{ order.purchase_order_id }}</h6>
                                    <small class="text-muted">{{ formatDate(order.created_at) }}</small>
                                </div>
                                <div class="col-md-2">
                                    <span 
                                        class="badge"
                                        :class="getStatusBadgeClass(order.status)"
                                    >
                                        {{ formatStatus(order.status) }}
                                    </span>
                                </div>
                                <div class="col-md-2">
                                    <span 
                                        class="badge"
                                        :class="getOrderTypeBadgeClass(order.order_type)"
                                    >
                                        {{ formatOrderType(order.order_type) }}
                                    </span>
                                </div>
                                <div class="col-md-2">
                                    <strong>${{ order.total ? parseFloat(order.total).toFixed(2) : '0.00' }}</strong>
                                </div>
                                <div class="col-md-3 text-end">
                                    <Link 
                                        :href="`/orders/confirmed/${order.purchase_order_id}`"
                                        class="btn btn-outline-primary btn-sm"
                                    >
                                        View Details
                                    </Link>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="card-subtitle mb-2 text-muted">Customer Information</h6>
                                    <p class="mb-1"><strong>{{ order.user ? `${order.user.first_name} ${order.user.last_name}` : `${order.first_name} ${order.last_name}` }}</strong></p>
                                    <p class="mb-1 text-muted small">{{ order.user ? order.user.email : order.email }}</p>
                                    <p class="mb-0 text-muted small">{{ order.user ? order.user.phone : order.phone }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="card-subtitle mb-2 text-muted">Order Items ({{ order.order_items ? order.order_items.length : 0 }})</h6>
                                    <div class="d-flex flex-wrap gap-1">
                                        <span 
                                            v-for="item in (order.order_items || []).slice(0, 3)" 
                                            :key="item.id"
                                            class="badge bg-light text-dark"
                                        >
                                            {{ item.food_item ? item.food_item.name : 'Unknown Item' }} ({{ item.quantity }})
                                        </span>
                                        <span 
                                            v-if="order.order_items && order.order_items.length > 3"
                                            class="badge bg-secondary"
                                        >
                                            +{{ order.order_items.length - 3 }} more
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="order.notes" class="mt-3">
                                <h6 class="card-subtitle mb-2 text-muted">Order Notes</h6>
                                <p class="text-muted small mb-0">{{ order.notes }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'

defineProps({
    orders: Array
})

const formatStatus = (status) => {
    return status.split('_').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ')
}

const formatOrderType = (type) => {
    const types = {
        delivery: 'Delivery',
        takeaway: 'Takeaway',
        pay_on_spot: 'Pay on Spot'
    }
    return types[type] || type
}

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString()
}

const getStatusBadgeClass = (status) => {
    const classes = {
        'order_in_progress': 'bg-warning',
        'order_placed': 'bg-info',
        'confirmed': 'bg-success',
        'cancelled': 'bg-danger'
    }
    return classes[status] || 'bg-secondary'
}

const getOrderTypeBadgeClass = (orderType) => {
    const classes = {
        'delivery': 'bg-primary',
        'takeaway': 'bg-success',
        'pay_on_spot': 'bg-warning'
    }
    return classes[orderType] || 'bg-secondary'
}
</script>
