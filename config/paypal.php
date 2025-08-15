<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PayPal Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for PayPal integration
    |
    */

    'client_id' => env('PAYPAL_CLIENT_ID'),
    'client_secret' => env('PAYPAL_CLIENT_SECRET'),
    'mode' => env('PAYPAL_MODE', 'sandbox'), // live or sandbox
    'currency' => env('PAYPAL_CURRENCY', 'USD'),
    
    // API URLs
    'base_uri' => env('PAYPAL_MODE', 'sandbox') === 'live' 
        ? 'https://api.paypal.com' 
        : 'https://api.sandbox.paypal.com',
    
    // Webhook configuration
    'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
    
    // PayPal JS SDK configuration for frontend
    'js_config' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'currency' => env('PAYPAL_CURRENCY', 'USD'),
        'intent' => 'capture',
        'components' => 'buttons',
    ],
];