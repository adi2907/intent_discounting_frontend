<?php 

return [
    "API_KEY" => env("SHOPIFY_API_KEY", "780bcd701c81404bef2fe2ca9509f97e"),
    "SECRET_KEY" => env('SHOPIFY_SECRET_KEY','e19aea84ff380dd07f1eb8948a7ad55f'),
    "API_VERSION" => env("API_VERSION", "2023-07"),
    "ENC_SECRET_KEY" => "dipak_almee",
    "APP_SCOPES" => env('SHOPIFY_APP_SCOPES', '')
];