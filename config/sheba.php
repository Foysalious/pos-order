<?php

return [
    'whitelisted_ip_redis_key_name' => env('WHITELISTED_IP_REDIS_KEY_NAME', 'WHITELISTED_IPS'),
    'notification_services_app_key' => env('SHEBA_NOTIFICATION_SERVICES_APP_KEY', 'sheba1234'),
    'notification_services_app_secret' => env('SHEBA_NOTIFICATION_SERVICES_APP_SECRET', 'sheba1234'),
    'api_url' => env('SHEBA_API_URL'),
    'smanager_settings_api_url' => env('SMANAGER_SETTINGS_SERVICE_API_URL', 'https://settings-smanager-webstore.dev-sheba.xyz'),
];
