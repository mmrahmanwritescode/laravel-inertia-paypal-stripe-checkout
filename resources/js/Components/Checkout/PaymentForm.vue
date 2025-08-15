<template>
    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">Payment Information</h4>
        </div>
        <div class="card-body">
            <div v-if="error" class="alert alert-danger" role="alert">
                {{ error }}
            </div>
            
            <div v-if="!elements" class="alert alert-info" role="alert">
                Payment system not ready
            </div>
            
            <!-- Stripe Elements will be mounted here -->
            <div id="payment-element" class="mb-4" style="min-height: 40px; padding: 10px; border: 1px solid #e0e0e0; border-radius: 4px; background: #f9f9f9;"></div>
            
            <div v-if="elements" class="alert alert-success small" role="alert">
                Payment form ready
            </div>
            
            <button 
                @click="handlePayment"
                :disabled="!stripeReady || processing || !elements"
                class="btn btn-primary w-100 py-3"
            >
                <span v-if="processing">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    Processing Payment...
                </span>
                <span v-else>Complete Order</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue'
import { loadStripe } from '@stripe/stripe-js'
import { router } from '@inertiajs/vue3'

console.log('PaymentForm component is loading!')

const props = defineProps({
    stripe: Object,
    paymentIntentId: String,
    clientSecret: String,
    processing: Boolean
})

const emit = defineEmits(['process-payment', 'payment-error'])

const stripe = ref(null)
const elements = ref(null)
const paymentElement = ref(null)
const stripeReady = ref(false)
const error = ref('')

let pendingConfirmation = null

onMounted(async () => {
    console.log('PaymentForm mounted!')
    console.log('PaymentForm props:', {
        stripe: props.stripe,
        paymentIntentId: props.paymentIntentId,
        clientSecret: props.clientSecret,
        processing: props.processing
    })
    
    await initializeStripe()
    // If we have a client secret already, set up the payment element
    if (props.clientSecret && stripe.value) {
        console.log('PaymentForm has client secret on mount, setting up element')
        await setupPaymentElement(props.clientSecret)
    } else {
        console.log('PaymentForm waiting for client secret...')
    }
})

// Watch for client secret changes
watch(() => props.clientSecret, async (newSecret) => {
    console.log('Client secret changed in PaymentForm:', { newSecret, hasStripe: !!stripe.value })
    if (newSecret && stripe.value) {
        console.log('Client secret received, setting up payment element')
        await setupPaymentElement(newSecret)
    } else if (!newSecret) {
        console.log('Client secret cleared')
    }
})

const initializeStripe = async () => {
    try {
        console.log('Initializing Stripe with key:', props.stripe?.publishable_key)
        
        if (!props.stripe?.publishable_key) {
            throw new Error('No Stripe publishable key provided')
        }
        
        stripe.value = await loadStripe(props.stripe.publishable_key)
        console.log('Stripe loaded:', stripe.value)
        
        if (!stripe.value) {
            throw new Error('Failed to initialize Stripe - loadStripe returned null')
        }
        
        stripeReady.value = true
        console.log('Stripe initialized successfully')
    } catch (err) {
        error.value = 'Failed to load payment system: ' + err.message
        console.error('Stripe initialization error:', err)
    }
}

const setupPaymentElement = async (clientSecret) => {
    console.log('Setting up payment element with client secret:', clientSecret)
    console.log('Stripe instance:', stripe.value)
    console.log('Elements instance before:', elements.value)
    
    if (!stripe.value || !clientSecret) {
        console.log('Missing stripe or client secret', { 
            hasStripe: !!stripe.value, 
            hasSecret: !!clientSecret 
        })
        return
    }
    
    try {
        // Clear any existing elements
        if (paymentElement.value) {
            console.log('Unmounting existing payment element')
            paymentElement.value.unmount()
        }
        
        const appearance = {
            theme: 'stripe',
            variables: {
                colorPrimary: '#0570de',
                colorBackground: '#ffffff',
                colorText: '#30313d',
                colorDanger: '#df1b41',
                fontFamily: 'Ideal Sans, system-ui, sans-serif',
                spacingUnit: '2px',
                borderRadius: '4px',
            }
        }
        
        console.log('Creating Stripe elements with appearance:', appearance)
        elements.value = stripe.value.elements({ 
            clientSecret, 
            appearance 
        })
        console.log('Elements created:', elements.value)
        
        console.log('Creating payment element')
        paymentElement.value = elements.value.create('payment', {
            layout: 'tabs'
        })
        console.log('Payment element created:', paymentElement.value)
        
        await nextTick()
        console.log('Mounting payment element to #payment-element')
        const mountResult = paymentElement.value.mount('#payment-element')
        console.log('Mount result:', mountResult)
        console.log('Payment element mounted successfully')
        
        // Listen for element ready event
        paymentElement.value.on('ready', () => {
            console.log('Payment element is ready')
        })
        
        paymentElement.value.on('change', (event) => {
            if (event.error) {
                error.value = event.error.message
                console.error('Payment element error:', event.error)
            } else {
                error.value = ''
                console.log('Payment element changed:', event)
            }
        })
        
    } catch (err) {
        error.value = 'Failed to setup payment form'
        console.error('Payment element setup error:', err)
    }
}

const confirmPayment = async (confirmationData) => {
    pendingConfirmation = confirmationData
    
    if (!elements.value || !stripe.value) {
        error.value = 'Payment system not ready'
        return
    }
    
    try {
        const result = await stripe.value.confirmPayment({
            elements: elements.value,
            confirmParams: {
                return_url: `${window.location.origin}/orders/confirmed/${confirmationData.purchase_order_id}`,
            },
        })
        
        if (result.error) {
            if (result.error.type === "card_error" || result.error.type === "validation_error") {
                error.value = result.error.message
                // Emit event to reset processing state in parent
                emit('payment-error', result.error.message)
            } else {
                error.value = "An unexpected error occurred."
                // Emit event to reset processing state in parent
                emit('payment-error', error.value)
                // Reinitialize on unexpected errors
                await reinitializePayment()
            }
        }
    } catch (err) {
        error.value = "Payment confirmation failed"
        console.error('Payment confirmation error:', err)
        // Emit event to reset processing state in parent
        emit('payment-error', error.value)
    }
}

const handlePayment = async () => {
    if (!stripeReady.value || props.processing) return
    
    error.value = ''
    // Emit event to parent component
    emit('process-payment')
}

const reinitializePayment = async () => {
    // Clear existing elements
    if (paymentElement.value) {
        paymentElement.value.unmount()
    }
    elements.value = null
    paymentElement.value = null
    
    // Wait a bit and reinitialize
    setTimeout(async () => {
        if (pendingConfirmation) {
            await setupPaymentElement(pendingConfirmation.clientSecret)
        }
    }, 1000)
}

// Expose methods to parent component
defineExpose({
    confirmPayment,
    setupPaymentElement
})
</script>
