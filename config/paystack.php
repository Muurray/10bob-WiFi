<?php

return [
    'public_key' => env('PAYSTACK_PUBLIC_KEY'),
    'secret_key' => env('PAYSTACK_SECRET_KEY'),
    'currency' => env('PAYSTACK_CURRENCY', 'KES'),
    'base_url' => 'https://api.paystack.co',
];