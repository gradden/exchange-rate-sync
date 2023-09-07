<?php

use Illuminate\Support\Carbon;

return [
    'base_api_path' => 'https://data-api.ecb.europa.eu/service/data/',

    'currency' => env('ECB_CURRENCY', 'HUF'),
    'currency_denominator' => env('ECB_CURRENCY_DENOMINATOR', 'EUR'),

    'exchange_rate_type' => env('ECB_EXCHANGE_RATE_TYPE', 'SP00'),
    'exchange_rate_context' => env('ECB_EXCHANGE_RATE_CONTEXT', 'A'),

    'query-settings' => [
        'detail' => 'dataonly',
        'format' => 'jsondata',
    ],

    'update-params' => [
        'lastNObservations' => 1
    ],

    'daily-cron-time' => [
        '01:00',
        '10:00',
        '13:00'
    ]
];
