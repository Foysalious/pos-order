<?php

return [
    'api_url' => env('SHEBA_SERVICES_URL'),
    'sheba_services_vendor_id' => env('SHEBA_SERVICES_VENDOR_ID', '60cb5b0f6fe71675a8564683'),
    'sheba_push_notifications_account_id' => env('SHEBA_PUSH_NOTIFICATIONS_ACCOUNT_ID', '60cb5b37deda99001d7b7fd4'),
    'send_push_notifications' => env('SHEBA_SEND_PUSH_NOTIFICATIONS', true),
    'push_notification_topic_name' => [
        'customer' => env('CUSTOMER_TOPIC_NAME', 'customer_'),
        'resource' => env('RESOURCE_TOPIC_NAME', 'resource_'),
        'manager' => env('MANAGER_TOPIC_NAME', 'manager_'),
        'manager_new' => env('MANAGER_TOPIC_NAME_NEW', 'manager_new_'),
        'employee' => env('EMPLOYEE_TOPIC_NAME', 'employee_'),
        'affiliate' => env('AFFILIATE_TOPIC_NAME', 'affiliate_')
    ],
    'push_notification_channel_name' => [
        'customer' => 'customer_channel',
        'manager' => 'manager_channel',
        'resource' => 'resource_channel',
        'employee' => 'employee_channel',
        'affiliate' => 'affiliate_channel'
    ],
    'push_notification_sound' => [
        'customer' => 'default',
        'manager' => 'notification_sound',
        'affiliate' => 'default',
        'employee'  => 'notification_sound.aiff'
    ],
];
