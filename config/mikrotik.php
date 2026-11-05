<?php

return [

    'host' => env('MIKROTIK_HOST'),

    'username' => env('MIKROTIK_USERNAME'),

    'password' => env('MIKROTIK_PASSWORD'),

    'port' => env('MIKROTIK_PORT',8728),

    'ssl' => (bool) env('MIKROTIK_SSL', false),

    'profile' => env('MIKROTIK_PROFILE'),

    'multi_device_profile' => env('MIKROTIK_MULTI_DEVICE_PROFILE'),

    'portal_host' => env('MIKROTIK_PORTAL_HOST', env('MIKROTIK_HOST')),

];