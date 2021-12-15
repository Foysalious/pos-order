<?php

return [
    'own_delivery' => [
     'name' => 'own_delivery',
     'image' => null
    ],
    'paperfly' => [
       'name' => 'paperfly',
       'image' => env('S3_URL').'pos/paperfly.png'
    ]
];
