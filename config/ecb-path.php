<?php

use Illuminate\Support\Carbon;

return [
    'base_api_path' => 'https://data-api.ecb.europa.eu/service/data/',

    'currency' => env('ECB_CURRENCY', 'HUF'),
    'currency_denominator' => env('ECB_CURRENCY_DENOMINATOR', 'EUR'),

    'exchange_rate_type' => env('ECB_EXCHANGE_RATE_TYPE', 'SP00'),
    'exchange_rate_context' => env('ECB_EXCHANGE_RATE_CONTEXT', 'A'),

    'update-params' => [
        'detail' => 'dataonly',
        'format' => 'jsondata',
        'updatedAfter' => Carbon::now()->setTimezone('Europe/Budapest')->subDay()->toAtomString()
    ],

    'init-params' => [
        'detail' => 'dataonly',
        'format' => 'jsondata',
        'lastNObservations' => 1
    ]
];
