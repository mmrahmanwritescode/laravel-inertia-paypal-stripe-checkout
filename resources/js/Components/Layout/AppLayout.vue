<template>
    <div class="min-vh-100 bg-light">
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container">
                <Link class="navbar-brand fw-bold" href="/">
                    Food Ordering
                </Link>
                <div class="navbar-nav ms-auto">
                    <Link class="nav-link me-3" href="/">
                        Cart ({{ cart.count }})
                    </Link>
                    <Link class="nav-link me-3" href="/orders">
                        Orders
                    </Link>
                    <Link class="btn btn-primary" href="/checkout">
                        Checkout
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="container py-4">
            <!-- Flash Messages -->
            <div v-if="flash.message" class="alert alert-info m-3" role="alert">
                {{ flash.message }}
            </div>
            <div v-if="flash.error" class="alert alert-danger m-3" role="alert">
                {{ flash.error }}
            </div>
            <div v-if="flash.success" class="alert alert-success m-3" role="alert">
                {{ flash.success }}
            </div>            
            <slot />
        </main>
    </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const cart = computed(() => page.props.cart)
const flash = computed(() => page.props.flash)
</script>
