<template>
    <AppLayout>
        <div>
            <h1 class="h2 fw-bold text-dark mb-4">Checkout</h1>
            
            <!-- Show error if provided -->
            <div v-if="error" class="alert alert-danger mb-4">
                {{ error }}
            </div>
            
            <div v-if="cartData.items.length === 0" class="text-center py-5">
                <p class="text-muted fs-5">Your cart is empty</p>
                <Link href="/" class="btn btn-primary btn-lg mt-3">
                    Continue Shopping
                </Link>
            </div>
            
            <div v-else class="row g-5">
                <!-- Checkout Form -->
                <div class="col-lg-7">
                    <CheckoutForm 
                        :form="form"
                        :processing="processing"
                        @submit="handleSubmit"
                        @order-type-changed="handleOrderTypeChange"
                    />
                </div>
                
                <!-- Order Summary & Payment -->
                <div class="col-lg-5">
                    <OrderSummary 
                        :cartData="cartData"
                        :shipping-cost="shippingCost"
                    />
                    
                    <!-- Payment Method Selection -->
                    <div v-if="form.order_type !== 'pay_on_spot'" class="mt-4">
                        <div class="mb-3">
                            <label class="form-label">Select Payment Method:</label>
                            <select v-model="form.payment_method" class="form-select">
                                <option value="paypal">PayPal</option>
                                <option value="stripe">Stripe</option>
                            </select>
                        </div>
                        <div v-if="form.payment_method === 'paypal'">
                            <PayPalPaymentForm 
                                ref="paymentFormRef"
                                :paypal="paypal"
                                :paypal-order-id="paypalOrderId"
                                :processing="processing"
                                @process-payment="handlePayPalPayment"
                                @payment-error="handlePaymentError"
                            />
                        </div>
                        <div v-else-if="form.payment_method === 'stripe'">
                            <PaymentForm
                                ref="stripeFormRef"
                                :stripe="stripeConfig"
                                :client-secret="stripeClientSecret"
                                :processing="processing"
                                @process-payment="handleStripePayment"
                                @payment-error="handlePaymentError"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Components/Layout/AppLayout.vue'
import CheckoutForm from '@/Components/Checkout/CheckoutForm.vue'
import PayPalPaymentForm from '@/Components/Checkout/PayPalPaymentForm.vue'
import PaymentForm from '@/Components/Checkout/PaymentForm.vue'
import OrderSummary from '@/Components/Checkout/OrderSummary.vue'

const props = defineProps({
    cartData: Object,
    paypal: Object,
    stripe: Object,
    error: String
})

const paymentFormRef = ref(null)
const stripeFormRef = ref(null)
const paypalOrderId = ref(null)
const approvalUrl = ref(null)
const processing = ref(false)
const stripeClientSecret = ref(null)
const stripeConfig = { publishable_key: props.stripe?.publishable_key || '' }

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    address: '',
    post_code: '',
    notes: '',
    shipping_cost: 5.00,
    order_type: 'delivery',
    payment_method: 'paypal'
})
// Watch for payment method changes and create Stripe payment intent if needed
watch(() => form.payment_method, async (newMethod, oldMethod) => {
    if (newMethod === 'stripe' && props.cartData.items.length > 0) {
        await createStripePaymentIntentForUI()
    } else {
        stripeClientSecret.value = null
    }
})
const createStripePaymentIntentForUI = async () => {
    try {
        const total = props.cartData.summary.total + shippingCost.value
        const response = await window.axios.post('/checkout/stripe-order', {
            total_price: total
        })
        stripeClientSecret.value = response.data.client_secret
    } catch (error) {
        console.error('Error creating Stripe payment intent:', error.response?.data?.error || error.message)
        stripeClientSecret.value = null
    }
}
const handleStripePayment = async () => {
    processing.value = true
    try {
        // Create order in backend
        const orderResponse = await createStripeOrderAndSave()
        if (orderResponse.error) {
            throw new Error(orderResponse.error)
        }
        // Confirm payment with Stripe
        if (stripeFormRef.value) {
            await stripeFormRef.value.confirmPayment({
                order_id: orderResponse.order_id,
                purchase_order_id: orderResponse.purchase_order_id,
                clientSecret: stripeClientSecret.value
            })
        }
    } catch (error) {
        console.error('Stripe payment error:', error)
        processing.value = false
    }
}
const createStripeOrderAndSave = async () => {
    try {
        const response = await window.axios.post('/checkout/stripe-order-save', {
            ...form.data(),
            payment_method: 'stripe',
            payment_intent_id: stripeClientSecret.value // Save intent id if needed
        })
        return response.data
    } catch (error) {
        console.error('Error in createStripeOrderAndSave AJAX:', error)
        // Handle validation errors from Laravel
        if (error.response?.status === 422 && error.response?.data?.errors) {
            // Set errors on the Inertia form object so they display correctly
            Object.keys(error.response.data.errors).forEach(field => {
                form.setError(field, error.response.data.errors[field])
            })
        }
        return { error: error.response?.data?.error || error.message }
    }
}

const shippingCost = computed(() => {
    return form.order_type === 'takeaway' ? 0 : 5.00
})

watch(() => form.order_type, (newType) => {
    form.shipping_cost = newType === 'takeaway' ? 0 : 5.00
})

// Create PayPal order when component mounts (if not pay on spot)
onMounted(async () => {
    console.log('Component mounted, order type:', form.order_type)
    console.log('Props received:', {
        cartData: props.cartData,
        paypal: props.paypal,
        error: props.error
    })
    
    // Only create PayPal order if cart has items and order type requires payment
    if (props.cartData.items.length > 0 && form.order_type !== 'pay_on_spot' && !paypalOrderId.value) {
        console.log('Creating PayPal order on mount...')
        await createPayPalOrderForUI()
    }
})

// Watch for order type changes
watch(() => form.order_type, async (newType, oldType) => {
    console.log('Order type changed:', { oldType, newType })
    
    if (newType === 'pay_on_spot') {
        // Clear PayPal order if switching to pay on spot
        console.log('Clearing PayPal order for pay on spot')
        paypalOrderId.value = null
    } else if (oldType === 'pay_on_spot' && !paypalOrderId.value && props.cartData.items.length > 0) {
        // Create PayPal order if switching from pay on spot to payment method
        console.log('Switched from pay on spot to payment method, creating PayPal order...')
        await createPayPalOrderForUI()
    } else if (newType !== 'pay_on_spot' && oldType !== 'pay_on_spot' && paypalOrderId.value) {
        // Recreate PayPal order if switching between delivery/takeaway (shipping cost changed)
        console.log('Order type changed between delivery/takeaway, recreating PayPal order with new total...')
        await createPayPalOrderForUI()
    }
})

// Watch for form readiness - keep this for debugging
watch(() => [form.first_name, form.last_name, form.email, form.phone], ([firstName, lastName, email, phone]) => {
    console.log('Form fields changed:', { firstName, lastName, email, phone, orderType: form.order_type })
}, { immediate: true })

const handleOrderTypeChange = (orderType) => {
    form.order_type = orderType
}

const handlePaymentError = (errorMessage) => {
    console.log('Payment error received:', errorMessage)
    processing.value = false
}

const createPayPalOrderForUI = async () => {
    try {
        const total = props.cartData.summary.total + shippingCost.value
        console.log('Creating PayPal order for total:', total)
        const orderResponse = await createPayPalOrder(total)
        
        console.log('PayPal order response:', orderResponse)
        if (orderResponse.error) {
            console.error('PayPal order error:', orderResponse.error)
            return
        }
        
        console.log('Setting paypalOrderId and approvalUrl...')
        paypalOrderId.value = orderResponse.id
        approvalUrl.value = orderResponse.approval_url
        
        console.log('Values set:', {
            paypalOrderId: paypalOrderId.value,
            approvalUrl: approvalUrl.value
        })
        
        // Set up PayPal payment element with order ID
        if (paymentFormRef.value && paypalOrderId.value) {
            console.log('Setting up PayPal payment element')
            await paymentFormRef.value.setupPayPalElement(paypalOrderId.value)
        } else {
            console.log('Cannot setup PayPal payment element:', {
                hasPaymentFormRef: !!paymentFormRef.value,
                hasPaypalOrderId: !!paypalOrderId.value
            })
        }
    } catch (error) {
        console.error('Error creating PayPal order:', error)
    }
}

const createPayPalOrderForForm = async () => {
    try {
        const total = props.cartData.summary.total + shippingCost.value
        console.log('Creating PayPal order for total:', total)
        const orderResponse = await createPayPalOrder(total)
        
        console.log('PayPal order response:', orderResponse)
        if (orderResponse.error) {
            console.error('PayPal order error:', orderResponse.error)
            return
        }
        
        console.log('Setting paypalOrderId and approvalUrl...')
        paypalOrderId.value = orderResponse.id
        approvalUrl.value = orderResponse.approval_url
        
        console.log('Values set:', {
            paypalOrderId: paypalOrderId.value,
            approvalUrl: approvalUrl.value
        })
        
        // Set up PayPal payment element with order ID
        if (paymentFormRef.value && paypalOrderId.value) {
            console.log('Setting up PayPal payment element')
            await paymentFormRef.value.setupPayPalElement(paypalOrderId.value)
        } else {
            console.log('Cannot setup PayPal payment element:', {
                hasPaymentFormRef: !!paymentFormRef.value,
                hasPaypalOrderId: !!paypalOrderId.value
            })
        }
    } catch (error) {
        console.error('Error creating PayPal order:', error)
    }
}

const handleSubmit = async () => {

    if (form.processing) return
    
    processing.value = true
    
    try {
        if (form.order_type === 'pay_on_spot') {
            // Direct form submission for pay on spot - let server handle validation
            form.post('/checkout/store', {
                onSuccess: (page) => {
                    processing.value = false
                },
                onError: (errors) => {
                    processing.value = false
                    // Inertia automatically handles validation errors
                }
            })
        } else {
            // Handle PayPal payment
            await handlePayPalPayment()
        }
    } catch (error) {
        console.error('Checkout error:', error)
        processing.value = false
    }
}

const handlePayPalPayment = async () => {
    processing.value = true
    
    try {
        // PayPal order should already be created, just proceed with customer and payment
        if (!paypalOrderId.value) {
            throw new Error('PayPal order not ready')
        }
        
        // Create customer and order
        const customerResponse = await createCustomerAndOrder()
        
        if (customerResponse.error) {
            throw new Error(customerResponse.error)
        }
        
        // Confirm payment with PayPal
        if (paymentFormRef.value) {
            await paymentFormRef.value.confirmPayPalPayment({
                order_id: customerResponse.order_id,
                purchase_order_id: customerResponse.purchase_order_id,
                customer_id: customerResponse.customer_id,
                paypalOrderId: paypalOrderId.value
            })
        }
    } catch (error) {
        console.error('Payment error:', error)
        processing.value = false
    }
}

const createPayPalOrder = async (total) => {
    
    try {
        console.log('Making AJAX call to create PayPal order for total:', total)
        
        const response = await window.axios.post('/checkout/paypal-order', {
            total_price: total
        })

        console.log('PayPal order AJAX response:', response.data)
        return response.data
    } catch (error) {
        console.error('Error in createPayPalOrder AJAX:', error)
        return { error: error.response?.data?.error || error.message }
    }
}

const createCustomerAndOrder = async () => {
    try {
        console.log('Making AJAX call to create customer and order')
        
        const response = await window.axios.post('/checkout/create-customer', {
            paypal_order_id: paypalOrderId.value,
            ...form.data()
        })

        console.log('Customer and order AJAX response:', response.data)
        return response.data
    } catch (error) {
        console.error('Error in createCustomerAndOrder AJAX:', error)
        // Handle validation errors from Laravel
        if (error.response?.status === 422 && error.response?.data?.errors) {
            // Set errors on the Inertia form object so they display correctly
            Object.keys(error.response.data.errors).forEach(field => {
                form.setError(field, error.response.data.errors[field])
            })
        }
        return { error: error.response?.data?.error || error.response?.data?.message || error.message }
    }
}
</script>
