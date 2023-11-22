<?php 

return [
    "API_KEY" => env("SHOPIFY_API_KEY"),
    "SECRET_KEY" => env('SHOPIFY_SECRET_KEY'),
    "API_VERSION" => env("API_VERSION", "2023-07"),
    "ENC_SECRET_KEY" => "dipak_almee",
    "APP_SCOPES" => env('SHOPIFY_APP_SCOPES', ''),
    "webhooks" => [
        'carts/update' => 'carts.update.webhook',
        'carts/create' => 'carts.create.webhook',
        'checkouts/create' => 'checkouts.create.webhook',
        'checkouts/update' => 'checkouts.update.webhook'
    ]
];