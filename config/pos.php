<?php

return [
    'payment_method' => ['cod', 'bkash', 'online', 'others', 'payment_link','qr_code','advance_balance','emi'],
    'minimum_order_amount_for_emi' => 5000,
    'payment_link_url' => env('SHEBA_PAYMENT_LINK_URL'),
    'payment_link_web_url' => env('SHEBA_PAYMENT_LINK_WEB_URL'),
];
