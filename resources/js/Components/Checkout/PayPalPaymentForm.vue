<template>
    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">Payment Information</h4>
        </div>
        <div class="card-body">
            <div v-if="error" class="alert alert-danger" role="alert">
                {{ error }}
            </div>
            
            <div v-if="!paypalReady" class="alert alert-info" role="alert">
                Payment system not ready
            </div>
            
            <!-- PayPal Smart Payment Buttons will be mounted here -->
            <div id="paypal-button-container" class="mb-4" style="min-height: 40px;"></div>
            
            <div v-if="paypalReady && paypalOrderId" class="alert alert-success small" role="alert">
                PayPal payment form ready
            </div>
            
            <button 
                v-if="!paypalReady || processing"
                :disabled="true"
                class="btn btn-primary w-100 py-3"
            >
                <span v-if="processing">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Processing Payment...
                </span>
                <span v-else>
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Loading PayPal...
                </span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { loadScript } from '@paypal/paypal-js'
import { router } from '@inertiajs/vue3'

console.log('PayPalPaymentForm component is loading!')

const props = defineProps({
    paypal: Object,
    paypalOrderId: String,
    approvalUrl: String,
    processing: Boolean
})

const emit = defineEmits(['process-payment', 'payment-error'])

const paypalSDK = ref(null)
const paypalReady = ref(false)
const error = ref('')
const paypalButtons = ref(null)
const isSettingUp = ref(false)

let pendingConfirmation = null

onMounted(async () => {
    console.log('PayPalPaymentForm mounted!')
    console.log('PayPalPaymentForm props:', {
        paypal: props.paypal,
        paypalOrderId: props.paypalOrderId,
        approvalUrl: props.approvalUrl,
        processing: props.processing
    })
    
    await initializePayPal()
    // If we have a PayPal order ID already, set up the payment buttons
    if (props.paypalOrderId && paypalSDK.value) {
        console.log('PayPalPaymentForm has order ID on mount, setting up buttons')
        await setupPayPalElement(props.paypalOrderId)
    } else {
        console.log('PayPalPaymentForm waiting for order ID...')
    }
})

// Watch for PayPal order ID changes
watch(() => props.paypalOrderId, async (newOrderId, oldOrderId) => {
    console.log('PayPal order ID changed in PayPalPaymentForm:', { newOrderId, oldOrderId, hasPayPal: !!paypalSDK.value, isSettingUp: isSettingUp.value })
    
    // Skip if already setting up to prevent duplicates
    if (isSettingUp.value) {
        console.log('PayPal buttons setup already in progress, skipping...')
        return
    }
    
    // Only setup if we have a new order ID and it's different from the old one
    if (newOrderId && newOrderId !== oldOrderId && paypalSDK.value) {
        console.log('PayPal order ID received, setting up payment buttons')
        await setupPayPalElement(newOrderId)
    } else if (!newOrderId) {
        console.log('PayPal order ID cleared, clearing buttons')
        await clearPayPalButtons()
    }
})

const initializePayPal = async () => {
    try {
        console.log('Initializing PayPal with client ID:', props.paypal?.client_id)
        
        if (!props.paypal?.client_id) {
            throw new Error('No PayPal client ID provided')
        }
        
        paypalSDK.value = await loadScript({
            'client-id': props.paypal.client_id,
            currency: props.paypal.currency || 'USD',
            intent: 'capture',
            components: 'buttons'
        })
        
        console.log('PayPal SDK loaded:', paypalSDK.value)
        
        if (!paypalSDK.value) {
            throw new Error('Failed to initialize PayPal - loadScript returned null')
        }
        
        paypalReady.value = true
        console.log('PayPal initialized successfully')
    } catch (err) {
        error.value = 'Failed to load payment system: ' + err.message
        console.error('PayPal initialization error:', err)
    }
}

const clearPayPalButtons = async () => {
    console.log('Clearing PayPal buttons')
    const container = document.getElementById('paypal-button-container')
    if (container) {
        container.innerHTML = ''
    }
    if (paypalButtons.value && typeof paypalButtons.value.close === 'function') {
        try {
            paypalButtons.value.close()
        } catch (e) {
            console.log('Error closing PayPal buttons:', e)
        }
    }
    paypalButtons.value = null
}

const setupPayPalElement = async (paypalOrderId) => {
    console.log('Setting up PayPal buttons with order ID:', paypalOrderId)
    console.log('PayPal SDK instance:', paypalSDK.value)
    
    if (!paypalSDK.value || !paypalOrderId || isSettingUp.value) {
        console.log('Cannot setup PayPal buttons:', { 
            hasPayPal: !!paypalSDK.value, 
            hasOrderId: !!paypalOrderId,
            isSettingUp: isSettingUp.value
        })
        return
    }
    
    try {
        isSettingUp.value = true
        console.log('Starting PayPal button setup...')
        
        // Clear any existing buttons first
        await clearPayPalButtons()
        
        console.log('Creating PayPal Smart Payment Buttons')
        
        paypalButtons.value = paypalSDK.value.Buttons({
            // Set up the order
            createOrder: function(data, actions) {
                console.log('PayPal createOrder called, returning existing order ID:', paypalOrderId)
                // Return the existing order ID created by our backend
                return paypalOrderId
            },
            
            // Handle payment approval
            onApprove: function(data, actions) {
                console.log('PayPal payment approved:', data)
                
                // Emit event to parent component to handle the approval
                emit('process-payment', {
                    orderID: data.orderID,
                    paymentID: data.paymentID,
                    payerID: data.payerID,
                    facilitatorAccessToken: data.facilitatorAccessToken
                })
                
                return actions.order.capture().then(function(orderData) {
                    console.log('PayPal payment captured:', orderData)
                    // The payment is completed, navigate to success page
                    if (pendingConfirmation) {
                        router.get(`/orders/confirmed/${pendingConfirmation.purchase_order_id}`)
                    }
                })
            },
            
            // Handle payment errors
            onError: function(err) {
                console.error('PayPal payment error:', err)
                error.value = 'Payment failed. Please try again.'
                emit('payment-error', error.value)
            },
            
            // Handle payment cancellation
            onCancel: function(data) {
                console.log('PayPal payment cancelled:', data)
                error.value = 'Payment was cancelled.'
                emit('payment-error', error.value)
            },
            
            // Button styling
            style: {
                layout: 'vertical',
                color: 'gold',
                shape: 'rect',
                label: 'paypal'
            }
        })
        
        await nextTick()
        console.log('Rendering PayPal buttons to #paypal-button-container')
        const renderResult = paypalButtons.value.render('#paypal-button-container')
        console.log('Render result:', renderResult)
        console.log('PayPal buttons rendered successfully')
        
    } catch (err) {
        error.value = 'Failed to setup payment form'
        console.error('PayPal buttons setup error:', err)
    } finally {
        isSettingUp.value = false
        console.log('PayPal button setup completed')
    }
}

const confirmPayPalPayment = async (confirmationData) => {
    pendingConfirmation = confirmationData
    
    if (!paypalSDK.value || !paypalButtons.value) {
        error.value = 'Payment system not ready'
        return
    }
    
    console.log('PayPal payment confirmation data stored:', confirmationData)
    // PayPal payment will be handled by the button's onApprove callback
    // This method just stores the confirmation data for use after payment approval
}

const handlePayment = async () => {
    if (!paypalReady.value || props.processing) return
    
    error.value = ''
    // Emit event to parent component
    emit('process-payment')
}

const reinitializePayment = async () => {
    // Clear existing buttons
    await clearPayPalButtons()
    
    // Wait a bit and reinitialize
    setTimeout(async () => {
        if (props.paypalOrderId && !isSettingUp.value) {
            await setupPayPalElement(props.paypalOrderId)
        }
    }, 1000)
}

// Cleanup on component unmount
onUnmounted(() => {
    console.log('PayPalPaymentForm unmounting, cleaning up buttons')
    clearPayPalButtons()
    isSettingUp.value = false
})

// Expose methods to parent component
defineExpose({
    confirmPayPalPayment,
    setupPayPalElement
})
</script>
