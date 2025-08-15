<template>
    <AppLayout>
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Success/Progress Icon -->
                    <div class="text-center mb-4">
                        <div 
                            class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" 
                            :class="order.status === 'confirmed' ? 'bg-success bg-opacity-10' : 'bg-warning bg-opacity-10'"
                            style="width: 64px; height: 64px;"
                        >
                            <i 
                                :class="order.status === 'confirmed' ? 'fas fa-check text-success' : 'fas fa-clock text-warning'" 
                                style="font-size: 2rem;"
                            ></i>
                        </div>
                        <h1 class="display-6 text-dark mb-3">
                            {{ order.status === 'confirmed' ? 'Order Confirmed!' : 'Order In Progress!' }}
                        </h1>
                        <p class="text-muted mb-4">
                            {{ getStatusMessage(order.status) }}
                        </p>
                    </div>
                    
                    <!-- Order Details -->
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title h5 mb-4">Order Details</h2>
                            
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-dark mb-2">Order Information</h6>
                                    <p class="text-muted small mb-1">Order #: {{ order.purchase_order_id }}</p>
                                    <p class="small mb-1">
                                        <span 
                                            class="badge" 
                                            :class="getStatusBadgeClass(order.status)"
                                        >
                                            Status: {{ formatStatus(order.status) }}
                                        </span>
                                    </p>
                                    <p class="text-muted small mb-1">Order Type: {{ formatOrderType(order.order_type) }}</p>
                                    <p class="text-muted small mb-1">Date: {{ formatDate(order.created_at) }}</p>
                                </div>
                                
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-dark mb-2">Customer Information</h6>
                                    <p class="text-muted small mb-1">{{ order.user.first_name }} {{ order.user.last_name }}</p>
                                    <p class="text-muted small mb-1">{{ order.user.email }}</p>
                                    <p class="text-muted small mb-1">{{ order.user.phone }}</p>
                                    <p v-if="order.order_type === 'delivery' && order.address" class="text-muted small mb-1">
                                        {{ order.address }}, {{ order.post_code }}
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Order Items -->
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark mb-3">Order Items</h6>
                                <div class="list-group list-group-flush">
                                    <div v-for="item in order.order_items" :key="item.id" class="list-group-item d-flex justify-content-between align-items-center px-0 border-bottom pb-3">
                                        <div>
                                            <div class="fw-medium">{{ item.food_item.name }}</div>
                                            <div class="text-muted small">Qty: {{ item.quantity }} × ${{ item.price }}</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-medium">${{ (item.price * item.quantity).toFixed(2) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Order Total -->
                            <div class="border-top pt-3">
                                <div class="row mb-2">
                                    <div class="col">
                                        <span class="text-muted">Subtotal</span>
                                    </div>
                                    <div class="col-auto">
                                        <span>${{ parseFloat(order.price).toFixed(2) }}</span>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col">
                                        <span class="text-muted">Shipping</span>
                                    </div>
                                    <div class="col-auto">
                                        <span v-if="order.shipping_cost > 0">${{ parseFloat(order.shipping_cost).toFixed(2) }}</span>
                                        <span v-else class="text-success">Free</span>
                                    </div>
                                </div>
                                <div class="row border-top pt-2">
                                    <div class="col">
                                        <span class="fw-bold fs-5">Total</span>
                                    </div>
                                    <div class="col-auto">
                                        <span class="fw-bold fs-5">${{ (parseFloat(order.price) + parseFloat(order.shipping_cost)).toFixed(2) }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payment Information -->
                            <div v-if="order.payment_method === 'paypal'" class="mt-4 pt-4 border-top">
                                <h6 class="fw-bold text-dark mb-2">Payment Information</h6>
                                <p class="text-muted small mb-1">Payment Method: PayPal</p>
                                <p class="text-muted small mb-1">Transaction ID: {{ order.transaction_id }}</p>
                                <div v-if="order.status === 'order_in_progress'" class="alert alert-info small mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Payment is being processed. Order status will be updated automatically once payment is confirmed.
                                </div>
                            </div>
                            
                            <!-- Pay on Spot Information -->
                            <div v-else-if="order.payment_method === 'N/A'" class="mt-4 pt-4 border-top">
                                <h6 class="fw-bold text-dark mb-2">Payment Information</h6>
                                <p class="text-muted small mb-1">Payment Method: Pay on Spot</p>
                                <div v-if="order.status === 'order_in_progress'" class="alert alert-warning small mt-2">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Please prepare cash payment for when you {{ order.order_type === 'delivery' ? 'receive your order' : 'pick up your order' }}.
                                </div>
                            </div>
                            
                            <!-- Order Status Management -->
                            <div v-if="canUpdateStatus(order.status)" class="mt-4 pt-4 border-top">
                                <h6 class="fw-bold text-dark mb-3">
                                    <i class="fas fa-cog me-1"></i>
                                    Order Management
                                </h6>
                                
                                <!-- Status Update Options -->
                                <div class="row g-3">
                                    <!-- Cancel Order -->
                                    <div v-if="canCancelOrder(order.status)" class="col-md-6">
                                        <div class="card border-danger">
                                            <div class="card-body text-center py-3">
                                                <i class="fas fa-times-circle text-danger mb-2" style="font-size: 1.5rem;"></i>
                                                <h6 class="card-title text-danger mb-2">Cancel Order</h6>
                                                <p class="card-text small text-muted mb-3">
                                                    Cancel this order if you no longer need it.
                                                    <span v-if="order.payment_method === 'paypal'">
                                                        Refund will be processed automatically.
                                                    </span>
                                                </p>
                                                <button 
                                                    @click="showCancelConfirmation = true"
                                                    :disabled="updating"
                                                    class="btn btn-outline-danger btn-sm"
                                                >
                                                    <span v-if="updating" class="spinner-border spinner-border-sm me-1"></span>
                                                    Cancel Order
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Confirm Order (for admin/testing purposes) -->
                                    <div v-if="canConfirmOrder(order.status)" class="col-md-6">
                                        <div class="card border-success">
                                            <div class="card-body text-center py-3">
                                                <i class="fas fa-check-circle text-success mb-2" style="font-size: 1.5rem;"></i>
                                                <h6 class="card-title text-success mb-2">Confirm Order</h6>
                                                <p class="card-text small text-muted mb-3">
                                                    Mark this order as confirmed and ready for preparation.
                                                </p>
                                                <button 
                                                    @click="updateOrderStatus('confirmed')"
                                                    :disabled="updating"
                                                    class="btn btn-outline-success btn-sm"
                                                >
                                                    <span v-if="updating" class="spinner-border spinner-border-sm me-1"></span>
                                                    Confirm Order
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Mark as Completed -->
                                    <div v-if="canCompleteOrder(order.status)" class="col-md-6">
                                        <div class="card border-primary">
                                            <div class="card-body text-center py-3">
                                                <i class="fas fa-flag-checkered text-primary mb-2" style="font-size: 1.5rem;"></i>
                                                <h6 class="card-title text-primary mb-2">Mark as Completed</h6>
                                                <p class="card-text small text-muted mb-3">
                                                    Mark this order as completed when {{ order.order_type === 'delivery' ? 'delivered' : 'picked up' }}.
                                                </p>
                                                <button 
                                                    @click="updateOrderStatus('confirmed')"
                                                    :disabled="updating"
                                                    class="btn btn-outline-primary btn-sm"
                                                >
                                                    <span v-if="updating" class="spinner-border spinner-border-sm me-1"></span>
                                                    Mark Completed
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Success/Error Messages -->
                                <div v-if="updateMessage" class="mt-3">
                                    <div 
                                        class="alert" 
                                        :class="updateSuccess ? 'alert-success' : 'alert-danger'"
                                        role="alert"
                                    >
                                        <i :class="updateSuccess ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle'" class="me-1"></i>
                                        {{ updateMessage }}
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Notes -->
                            <div v-if="order.notes" class="mt-4 pt-4 border-top">
                                <h6 class="fw-bold text-dark mb-2">Order Notes</h6>
                                <p class="text-muted small">{{ order.notes }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="text-center mt-4">
                        <div class="d-flex gap-3 justify-content-center">
                            <Link href="/" class="btn btn-primary px-4 py-2">
                                Continue Shopping
                            </Link>
                            <Link href="/orders" class="btn btn-outline-secondary px-4 py-2">
                                View All Orders
                            </Link>
                        </div>
                        
                        <!-- Additional Info for In Progress Orders -->
                        <div v-if="order.status === 'order_in_progress'" class="mt-4">
                            <div class="alert alert-light border">
                                <h6 class="fw-bold mb-2">
                                    <i class="fas fa-info-circle text-primary me-1"></i>
                                    What happens next?
                                </h6>
                                <div class="text-start small">
                                    <div v-if="order.payment_method === 'paypal'" class="mb-2">
                                        <strong>1. Payment Processing:</strong> Your payment is being verified by our payment provider.
                                    </div>
                                    <div class="mb-2">
                                        <strong>{{ order.payment_method === 'paypal' ? '2' : '1' }}. Order Preparation:</strong> 
                                        Once confirmed, our kitchen will start preparing your order.
                                    </div>
                                    <div class="mb-2">
                                        <strong>{{ order.payment_method === 'paypal' ? '3' : '2' }}. Delivery/Pickup:</strong>
                                        <span v-if="order.order_type === 'delivery'">Your order will be delivered to your address.</span>
                                        <span v-else-if="order.order_type === 'takeaway'">You can pick up your order from our location.</span>
                                        <span v-else>Please come to our location for payment and pickup.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cancel Confirmation Modal -->
        <div v-if="showCancelConfirmation" class="modal show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title text-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Cancel Order
                        </h5>
                        <button 
                            @click="showCancelConfirmation = false" 
                            type="button" 
                            class="btn-close"
                            :disabled="updating"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-times-circle text-danger mb-3" style="font-size: 3rem;"></i>
                            <h6>Are you sure you want to cancel this order?</h6>
                        </div>
                        
                        <div class="alert alert-warning small">
                            <strong>Order #{{ order.purchase_order_id }}</strong><br>
                            Total: ${{ (parseFloat(order.price) + parseFloat(order.shipping_cost)).toFixed(2) }}
                        </div>
                        
                        <div v-if="order.payment_method === 'paypal'" class="alert alert-info small">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Refund Information:</strong> If you paid via PayPal, the refund will be processed automatically and may take 3-5 business days to appear in your account.
                        </div>
                        
                        <div class="mb-3">
                            <label for="cancelReason" class="form-label small fw-bold">Reason for cancellation (optional):</label>
                            <select v-model="cancelReason" id="cancelReason" class="form-select">
                                <option value="">Select a reason...</option>
                                <option value="changed_mind">Changed my mind</option>
                                <option value="wrong_order">Ordered wrong items</option>
                                <option value="too_long_wait">Taking too long</option>
                                <option value="found_better_option">Found a better option</option>
                                <option value="payment_issue">Payment issues</option>
                                <option value="other">Other reason</option>
                            </select>
                        </div>
                        
                        <div v-if="cancelReason === 'other'" class="mb-3">
                            <textarea 
                                v-model="cancelReasonText"
                                class="form-control form-control-sm" 
                                rows="2" 
                                placeholder="Please specify the reason..."
                            ></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button 
                            @click="showCancelConfirmation = false" 
                            type="button" 
                            class="btn btn-secondary"
                            :disabled="updating"
                        >
                            Keep Order
                        </button>
                        <button 
                            @click="confirmCancelOrder()" 
                            type="button" 
                            class="btn btn-danger"
                            :disabled="updating"
                        >
                            <span v-if="updating" class="spinner-border spinner-border-sm me-1"></span>
                            Yes, Cancel Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'

const props = defineProps({
    order: Object
})

// Reactive data for status updates
const updating = ref(false)
const updateMessage = ref('')
const updateSuccess = ref(false)
const showCancelConfirmation = ref(false)
const cancelReason = ref('')
const cancelReasonText = ref('')

// Status management functions
const canUpdateStatus = (status) => {
    return ['order_in_progress', 'order_placed'].includes(status)
}

const canCancelOrder = (status) => {
    return ['order_in_progress','confirmed'].includes(status)
}

const canConfirmOrder = (status) => {
    return status === 'order_in_progress'
}

const canCompleteOrder = (status) => {
    return status === 'confirmed'
}

// Update order status
const updateOrderStatus = async (newStatus) => {
    if (updating.value) return
    
    updating.value = true
    updateMessage.value = ''
    
    try {
        await router.patch(`/orders/${props.order.purchase_order_id}/status`, {
            status: newStatus,
            reason: cancelReason.value || null,
            reason_text: cancelReasonText.value || null
        }, {
            onSuccess: (page) => {
                updateSuccess.value = true
                updateMessage.value = getSuccessMessage(newStatus)
                // The page will automatically update with new order data
            },
            onError: (errors) => {
                updateSuccess.value = false
                updateMessage.value = errors.message || 'Failed to update order status. Please try again.'
            },
            onFinish: () => {
                updating.value = false
                if (showCancelConfirmation.value) {
                    showCancelConfirmation.value = false
                    cancelReason.value = ''
                    cancelReasonText.value = ''
                }
            }
        })
    } catch (error) {
        updateSuccess.value = false
        updateMessage.value = 'An error occurred. Please try again.'
        updating.value = false
    }
}

// Confirm cancel order
const confirmCancelOrder = () => {
    updateOrderStatus('cancelled')
}

// Get success message for status update
const getSuccessMessage = (status) => {
    const messages = {
        'cancelled': 'Order has been successfully cancelled.',
        'confirmed': 'Order has been confirmed and is now being prepared.',
        'completed': 'Order has been marked as completed. Thank you!'
    }
    return messages[status] || 'Order status updated successfully.'
}

const getStatusMessage = (status) => {
    const messages = {
        'confirmed': 'Thank you for your order. Your payment has been confirmed and order is being prepared!',
        'order_in_progress': 'Thank you for your order. Your order has been placed and is currently being processed.',
        'order_placed': 'Thank you for your order. Your order has been placed successfully!',
        'cancelled': 'This order has been cancelled.',
        'completed': 'Your order has been completed successfully!'
    }
    return messages[status] || 'Thank you for your order.'
}

const getStatusBadgeClass = (status) => {
    const classes = {
        'confirmed': 'bg-success',
        'order_in_progress': 'bg-warning',
        'order_placed': 'bg-primary',
        'cancelled': 'bg-danger',
        'completed': 'bg-success'
    }
    return classes[status] || 'bg-secondary'
}

const formatStatus = (status) => {
    const statusMap = {
        'order_in_progress': 'In Progress',
        'order_placed': 'Placed',
        'confirmed': 'Confirmed',
        'cancelled': 'Cancelled',
        'completed': 'Completed'
    }
    return statusMap[status] || status.split('_').map(word => 
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
</script>
