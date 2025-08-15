<template>
    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">Billing Information</h4>
        </div>
        <div class="card-body">
            <!-- Display general errors -->
            <!-- <div v-if="Object.keys(form.errors).length > 0" class="alert alert-danger mb-3">
                <strong>Please correct the following errors:</strong>
                <ul class="mb-0 mt-2">
                    <li v-for="(error, field) in form.errors" :key="field">
                        {{ Array.isArray(error) ? error[0] : error }}
                    </li>
                </ul>
            </div> -->
            
            <form @submit.prevent="$emit('submit')" novalidate>
                <!-- Order Type -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Order Type</label>
                    <div class="d-grid gap-2">
                        <div class="form-check">
                            <input 
                                class="form-check-input"
                                type="radio" 
                                v-model="form.order_type" 
                                value="delivery"
                                @change="$emit('order-type-changed', 'delivery')"
                                id="delivery"
                            />
                            <label class="form-check-label" for="delivery">
                                Delivery (+$5.00)
                            </label>
                        </div>
                        <div class="form-check">
                            <input 
                                class="form-check-input"
                                type="radio" 
                                v-model="form.order_type" 
                                value="takeaway"
                                @change="$emit('order-type-changed', 'takeaway')"
                                id="takeaway"
                            />
                            <label class="form-check-label" for="takeaway">
                                Takeaway (Free)
                            </label>
                        </div>
                        <div class="form-check">
                            <input 
                                class="form-check-input"
                                type="radio" 
                                v-model="form.order_type" 
                                value="pay_on_spot"
                                @change="$emit('order-type-changed', 'pay_on_spot')"
                                id="pay_on_spot"
                            />
                            <label class="form-check-label" for="pay_on_spot">
                                Pay on Spot
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Name -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            v-model="form.first_name"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.first_name }"
                        />
                        <div v-if="form.errors.first_name" class="invalid-feedback">
                            {{ Array.isArray(form.errors.first_name) ? form.errors.first_name[0] : form.errors.first_name }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input 
                            type="text" 
                            v-model="form.last_name"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.last_name }"
                        />
                        <div v-if="form.errors.last_name" class="invalid-feedback">
                            {{ Array.isArray(form.errors.last_name) ? form.errors.last_name[0] : form.errors.last_name }}
                        </div>
                    </div>
                </div>
                
                <!-- Contact -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input 
                            type="email" 
                            v-model="form.email"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.email }"
                        />
                        <div v-if="form.errors.email" class="invalid-feedback">
                            {{ Array.isArray(form.errors.email) ? form.errors.email[0] : form.errors.email }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone <span class="text-danger">*</span></label>
                        <input 
                            type="tel" 
                            v-model="form.phone"
                            class="form-control"
                            :class="{ 'is-invalid': form.errors.phone }"
                        />
                        <div v-if="form.errors.phone" class="invalid-feedback">
                            {{ Array.isArray(form.errors.phone) ? form.errors.phone[0] : form.errors.phone }}
                        </div>
                    </div>
                </div>
                
                <!-- Address (show only for delivery) -->
                <div v-if="form.order_type === 'delivery'" class="mb-3">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Address <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                v-model="form.address"
                                class="form-control"
                                :class="{ 'is-invalid': form.errors.address }"
                            />
                            <div v-if="form.errors.address" class="invalid-feedback">
                                {{ Array.isArray(form.errors.address) ? form.errors.address[0] : form.errors.address }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Post Code <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                v-model="form.post_code"
                                class="form-control"
                                :class="{ 'is-invalid': form.errors.post_code }"
                            />
                            <div v-if="form.errors.post_code" class="invalid-feedback">
                                {{ Array.isArray(form.errors.post_code) ? form.errors.post_code[0] : form.errors.post_code }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Notes -->
                <div class="mb-3">
                    <label class="form-label">Order Notes</label>
                    <textarea 
                        v-model="form.notes"
                        rows="3"
                        class="form-control"
                        placeholder="Any special instructions..."
                    ></textarea>
                </div>
                
                <!-- Submit Button (only show for pay on spot) -->
                <button 
                    v-if="form.order_type === 'pay_on_spot'"
                    type="submit"
                    :disabled="processing"
                    class="btn btn-success w-100 py-3"
                >
                    <span v-if="processing">
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Processing...
                    </span>
                    <span v-else>Place Order</span>
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>

const props = defineProps({
    form: Object,
    processing: Boolean
})

defineEmits(['submit', 'order-type-changed'])
</script>
