<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'appExt/*',
        'appExt/mostViewed',
        'appExt/mostCarted',
        'appExt/carts',
        'appExt/recommendedForYou',
        'appExt/userLiked',
        'webhooks/register',
        'webhooks/cartUpdate',
        'webhooks/cartCreate',
        'webhooks/checkoutUpdate',
        'webhooks/orderCreate',
        'webhooks/orderUpdate',
        'webhooks/checkoutCreate',
        'sendCartContents',
        'gdpr/webhooks/customer_data_request',
        'gdpr/webhooks/customer_data_erasure',
        'gdpr/webhooks/shop_data_erasure'
    ];
}
