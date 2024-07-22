<?php 

return [
    'cacheKeys' => [
        'cronStatus' => 'Check:Cron:Status',
        'createDiscountCode' => 'Check:Create:Discount:Status',
        'checkAlmeScripts' => 'Alme:Scripts:Stores',
        'mapIps' => 'ipmap',
        "TEST_PAYMENTS" => env('TEST_PAYMENTS', "false"),
    ]
];