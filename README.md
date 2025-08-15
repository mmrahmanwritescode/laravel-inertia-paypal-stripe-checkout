# Laravel Inertia Vue3 Dual Payment Gateway System

A modern, full-featured food ordering system with dual payment gateway integration (PayPal & Stripe), built using Laravel 12, Inertia.js, Vue 3, and comprehensive webhook support.

## 🚀 Live Demo
**Try it now:** [https://laravel-inertia-paypal.laravelcs.com/checkout](https://laravel-inertia-paypal.laravelcs.com/checkout)

Experience the complete checkout flow with dual payment options - choose between PayPal and Stripe with real-time payment processing and webhook integration!

## Screenshots

![Laravel inertia stripe checkout](https://laravelcs.com/images/github/laravel-ineria-paypal-checkout.png)

## 🛠️ Technology Stack

### Backend
- **Laravel 12** - Latest PHP framework
- **Inertia.js Laravel** (v2.0) - Server-side adapter
- **PayPal REST API** (v2) - Payment processing with HTTP client
- **Stripe API** - Payment processing with Elements and webhooks  
- **MySQL** - Database
- **PHP 8.2+** - Modern PHP features

### Frontend
- **Vue 3** (v3.5.18) - Progressive JavaScript framework
- **Inertia.js Vue3** (v2.0.17) - Client-side adapter
- **Bootstrap 5** (v5.3.3) - UI framework
- **PayPal JavaScript SDK** (@paypal/paypal-js) - Client-side PayPal integration
- **Stripe Elements** (@stripe/stripe-js) - Client-side Stripe integration
- **Vite** (v7.0.4) - Build tool and dev server

## ✨ Key Features

### 🛒 Shopping Cart System
- **Session-based cart management**
- **Real-time cart count updates**
- **Add/remove items with AJAX**
- **Cart persistence across page reloads**
- **Automatic cart clearing after successful orders**

### 💳 Advanced Dual Payment Processing
- **Payment Gateway Selection:**
  - PayPal Smart Payment Buttons with multiple payment methods
  - Stripe Elements with card payments and Payment Intents
  - User can choose payment method during checkout
- **Multiple Order Types:**
  - Delivery (with shipping cost)
  - Takeaway (free pickup)  
  - Pay on Spot (no online payment)
- **PayPal Integration:**
  - PayPal Smart Payment Buttons
  - PayPal Orders API for secure order creation and capture
  - Multiple Payment Methods (PayPal, Pay Later, Debit/Credit Cards)
- **Stripe Integration:**
  - Stripe Elements for secure card processing
  - Payment Intents API for 3D Secure support
  - Real-time payment confirmation
- **Universal Features:**
  - Real-time payment validation
  - Automatic payment error handling
  - Dynamic order total calculation
  - Secure webhook processing for both gateways

### � Order Management System
- **Interactive Order Status Management:**
  - Cancel orders with reason tracking
  - Confirm orders for preparation
  - Mark orders as completed
  - Status-specific UI displays
- **Order Status Flow:**
  - `order_in_progress` → `order_placed` → `confirmed`
  - `order_in_progress` → `cancelled`
  - `confirmed` → `cancelled` (without stripe refund processing)
- **Smart Status Transitions:**
  - Validation prevents invalid status changes
  - Automatic refund initiation for PayPal payments (Ready for implementation)
  - Comprehensive cancellation reason tracking
- **User-Friendly Interface:**
  - Confirmation modals for critical actions
  - Loading states during status updates
  - Success/error message feedback
  - Order-specific action buttons

### 🔔 Comprehensive Webhook Integration
- **Dual Gateway Webhook Support:**
  - PayPal webhook events with signature verification
  - Stripe webhook events with signature verification
- **PayPal Webhook Events:**
  - `CHECKOUT.ORDER.APPROVED` - Order approval confirmation
  - `PAYMENT.CAPTURE.COMPLETED` - Payment successful completion
  - `PAYMENT.CAPTURE.DENIED` - Payment failure handling
  - `PAYMENT.CAPTURE.REFUNDED` - Refund processing
  - `CHECKOUT.ORDER.CANCELLED` - Order cancellation
- **Stripe Webhook Events:**
  - `payment_intent.succeeded` - Payment successful completion
  - `payment_intent.payment_failed` - Payment failure handling
  - `payment_intent.canceled` - Payment cancellation
- **Universal Features:**
  - Real-time order status updates
  - Complete event logging and tracking
  - Automatic order status synchronization
  - Secure webhook signature verification for both gateways

### 🎯 User Experience
- **Single Page Application** feel with Inertia.js
- **Server-side validation** with real-time error display
- **Responsive design** with Bootstrap 5
- **Loading states** and progress indicators
- **Error recovery** and retry mechanisms
- **Interactive order management** with status updates
- **Real-time feedback** for all user actions
- **Clean, modern UI/UX**

### 🔐 Security & Validation
- **Server-side form validation** with real-time error display
- **CSRF protection** for all forms and AJAX requests
- **Dual webhook signature verification** (PayPal & Stripe)
- **SQL injection prevention**
- **XSS protection**
- **Conditional validation** (delivery address requirements)
- **Payment Intent verification** for Stripe payments
- **Secure API key management** for both payment gateways

## 📋 Database Schema

### Tables Included
- **users** - Customer information
- **food_items** - Product catalog
- **cart_items** - Session-based shopping cart
- **orders** - Order management
- **order_items** - Order line items

### Key Relationships
```
users (1:n) orders (1:n) order_items (n:1) food_items
cart_items (n:1) food_items
orders (1:1) paypal_orders (via PayPal API)
```

## 🔧 Installation & Setup

### Clone Repository
```bash
git clone https://github.com/mmrahmanwritescode/laravel-inertia-paypal-stripe-checkout
cd laravel-inertia-paypal-stripe-checkout
```

### 2. Backend Setup
```bash
# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Database Configuration
```bash
# Create MySQL database
# Update .env with database credentials:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Run migrations with demo data
php artisan migrate:fresh --seed
```

### 4. Dual Payment Gateway Configuration

#### PayPal Configuration
```bash
# Add to .env file:
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_CLIENT_SECRET=your_paypal_client_secret
PAYPAL_MODE=sandbox  # or 'live' for production
PAYPAL_WEBHOOK_ID=your_webhook_id
PAYPAL_CURRENCY=USD
```

#### Stripe Configuration
```bash
# Add to .env file:
STRIPE_PUBLISHABLE_KEY=your_stripe_publishable_key
STRIPE_SECRET_KEY=your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=your_stripe_webhook_secret
```

### 5. Frontend Setup
```bash
# Install Node.js dependencies
npm install

# Build for development
npm run dev

# Or build for production
npm run build
```

### 6. Dual Webhook Setup

#### PayPal Webhook Configuration
1. **PayPal Developer Dashboard Configuration:**
   - Go to PayPal Developer Dashboard → My Apps & Credentials
   - Select your app → Webhooks tab
   - Add endpoint: `https://yourdomain.com/paypal/webhook`
   - Select events: `CHECKOUT.ORDER.APPROVED`, `PAYMENT.CAPTURE.COMPLETED`, `PAYMENT.CAPTURE.DENIED`, `PAYMENT.CAPTURE.REFUNDED`
   - Copy webhook ID to `.env`

#### Stripe Webhook Configuration  
2. **Stripe Dashboard Configuration:**
   - Go to Stripe Dashboard → Developers → Webhooks
   - Add endpoint: `https://yourdomain.com/stripe/webhook`
   - Select events: `payment_intent.succeeded`, `payment_intent.payment_failed`, `payment_intent.canceled`
   - Copy webhook secret to `.env` as `STRIPE_WEBHOOK_SECRET`

#### Local Development Testing
3. **Local Development (using ngrok for testing):**
```bash
# Install ngrok and expose local server
ngrok http 8000

# Update webhook URLs in both PayPal and Stripe dashboards
# PayPal: https://your-ngrok-url.ngrok.io/paypal/webhook
# Stripe: https://your-ngrok-url.ngrok.io/stripe/webhook

# Copy webhook credentials to .env
PAYPAL_WEBHOOK_ID=your_paypal_webhook_id
STRIPE_WEBHOOK_SECRET=your_stripe_webhook_secret
```

### 7. Start Development Servers
```bash
# Terminal 1: Laravel development server
php artisan serve

# Terminal 2: Vite development server  
npm run dev

# Browse to: http://localhost:8000
```

## 🔄 Order Status Management

### Available Status Types
- **`order_in_progress`** - Initial status before order placement
- **`order_placed`** - Status after order placement
- **`confirmed`** - Order confirmed and ready for preparation ( then delivered/picked up )
- **`cancelled`** - Order cancelled by customer or restaurant

### Status Transition Rules
```
order_in_progress → order_placed | cancelled
order_placed → confirmed | cancelled
```

### Interactive Management Features
- **Cancel Orders:**
  - Reason selection (changed mind, wrong order, too long wait, etc.)
  - Custom reason text input
  - Automatic refund processing for PayPal payments ( Not added yet )
  - Confirmation modal with order details
  
- **Confirm Orders:**
  - Quick status update from in-progress to confirmed
  - Immediate UI feedback with success messages
  - Kitchen notification preparation
  
- **Complete Orders:**
  - Mark confirmed orders as confirmed
  - Useful for tracking delivery/pickup completion
  - Final status in order lifecycle

### Security & Validation
- **Status Transition Validation** - Prevents invalid status changes
- **Authorized Updates Only** - Secure endpoint protection
- **Comprehensive Logging** - All status changes tracked
- **Error Handling** - Graceful failure recovery

## 🔄 Dual Payment Flow

### Delivery/Takeaway Orders (PayPal Payment)
1. **User selects PayPal payment** → PayPal Smart Buttons initialized
2. **User fills checkout form** → Client-side validation
3. **PayPal Order created** → Server generates PayPal Order via REST API
4. **Form submission** → Server-side validation and order creation
5. **Customer & Order created** → Database records with PayPal reference
6. **Payment processing** → PayPal handles payment approval and capture
7. **Webhook confirmation** → Real-time status updates via PayPal webhooks
8. **Success redirect** → Order confirmation page
9. **Order management** → Interactive status updates (cancel/confirm/complete)

### Delivery/Takeaway Orders (Stripe Payment)
1. **User selects Stripe payment** → Payment Intent created
2. **User fills checkout form** → Client-side validation  
3. **Stripe Elements initialized** → Secure card input form
4. **Form submission** → Server-side validation and order creation
5. **Payment confirmation** → Stripe Elements handles 3D Secure if needed
6. **Payment processing** → Stripe processes payment with Payment Intent
7. **Webhook confirmation** → Real-time status updates via Stripe webhooks
8. **Success redirect** → Order confirmation page
9. **Order management** → Interactive status updates (cancel/confirm/complete)

### Pay on Spot Orders
1. **User fills checkout form** → Client-side validation
2. **Form submission** → Server-side validation with proper error rendering
3. **Order creation** → Direct database storage (no payment processing)
4. **Success redirect** → Order confirmation page
5. **Order management** → Interactive status updates (cancel/confirm/complete)

## 📁 Project Structure

### Backend Architecture
```
app/
├── Http/Controllers/
│   ├── CartController.php              # Shopping cart operations
│   ├── CheckoutController.php          # Dual payment processing
│   ├── OrderController.php             # Order management  
│   ├── PayPalWebhookController.php     # PayPal webhook handling
│   └── StripeWebhookController.php     # Stripe webhook handling
├── Models/
│   ├── User.php                        # Customer model
│   ├── FoodItem.php                   # Product model
│   ├── CartItem.php                   # Cart model
│   ├── Order.php                      # Order model with dual payment support
│   └── OrderItem.php                  # Order items model
├── Services/
│   ├── PayPalService.php              # PayPal integration service
│   └── StripeService.php              # Stripe integration service
└── Helpers/
    └── CartHelpers.php                # Cart utility functions
```

### Frontend Architecture
```
resources/js/
├── Components/
│   ├── Layout/
│   │   └── AppLayout.vue              # Main layout component
│   └── Checkout/
│       ├── CheckoutForm.vue           # Billing information form
│       ├── PaymentForm.vue            # Stripe Elements integration
│       ├── PayPalPaymentForm.vue      # PayPal Smart Buttons integration
│       └── OrderSummary.vue           # Cart summary display
└── Pages/
    ├── Cart/
    │   └── Index.vue                  # Shopping cart page
    ├── Checkout/
    │   └── Show.vue                   # Dual payment checkout page
    └── Orders/
        ├── Index.vue                  # Order history
        └── Confirmed.vue              # Order confirmation
```

## 🧪 Testing

### Dual Payment Gateway Testing

#### PayPal Testing (Sandbox Accounts)
```bash
# Use PayPal Developer Dashboard Sandbox accounts:
# Personal Account: buyer@example.com / password
# Business Account: merchant@example.com / password

# Test different PayPal payment scenarios in sandbox mode
```

#### Stripe Testing (Test Cards)
```bash
# Use Stripe test card numbers:
# Successful payment: 4242424242424242
# Declined payment: 4000000000000002
# 3D Secure required: 4000002500003155

# Test different Stripe payment scenarios in test mode
```

### Webhook Testing
```bash
# Test webhooks locally using ngrok for both gateways

# PayPal webhook testing:
# - Complete a sandbox PayPal payment
# - Cancel a PayPal payment mid-flow
# - Check webhook event logs in Laravel log files

# Stripe webhook testing:  
# - Complete a test Stripe payment
# - Use Stripe CLI for webhook testing: stripe listen --forward-to localhost:8000/stripe/webhook
# - Check webhook event logs in Laravel log files
```

## 🔍 Monitoring & Logging

### Webhook Events
- All webhook events are logged in `storage/logs/laravel.log`
- Order status changes are tracked
- Payment failures are recorded with reasons
- Dispute events are captured for review

### Error Handling
- Graceful payment error recovery
- User-friendly error messages
- Comprehensive error logging
- Automatic retry mechanisms

## 🚀 Production Deployment

### Environment Setup
```bash
# Set production environment
APP_ENV=production
APP_DEBUG=false

# Configure production database
# Set production PayPal keys (live mode)
# Set production Stripe keys (live mode)
# Configure both webhook URLs for production domain
```

### Build Assets
```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📖 API Endpoints

### Checkout Endpoints
- `GET /checkout` - Dual payment checkout page
- `POST /checkout/paypal-order` - Create PayPal order
- `POST /checkout/create-customer` - Create customer and PayPal order
- `POST /checkout/paypal-status` - Handle PayPal payment status
- `POST /checkout/stripe-order` - Create Stripe Payment Intent
- `POST /checkout/stripe-order-save` - Create Stripe order and save
- `POST /checkout/stripe-status` - Handle Stripe payment status
- `POST /checkout/store` - Store pay-on-spot orders

### Order Endpoints  
- `GET /orders` - View order history
- `GET /orders/confirmed/{purchaseOrderId}` - Order confirmation page
- `PATCH /orders/{purchaseOrderId}/status` - Update order status

### Cart Endpoints  
- `GET /cart` - View cart
- `POST /cart/add` - Add item to cart
- `DELETE /cart/remove/{id}` - Remove cart item
- `POST /clear-cart` - Clear entire cart

### Webhook Endpoints
- `POST /paypal/webhook` - PayPal webhook handler
- `POST /stripe/webhook` - Stripe webhook handler

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🔗 Related Resources

### Tutorials & Documentation
- [A Step-by-Step Guide on Laravel Checkout System with Stripe](https://laravelcs.com/communities/projects/topics/stripe/posts/192)
- [Building mini ecommerce in Laravel](https://laravelcs.com/communities/projects/topics/mini-ecommerce/posts/113)
- [Building mini issue tracker with Vue3 SPA in Laravel](https://laravelcs.com/communities/projects/topics/mini-issue-tracker/posts/159)

### More Projects
Visit [Laravelcs.com](https://laravelcs.com) for more Laravel tutorials and projects.

### Freelance Work
Available for custom Laravel development projects. Contact: [mahfoozurrahman.com](https://www.mahfoozurrahman.com)

---

⭐ **If this project helped you, please give it a star!** ⭐