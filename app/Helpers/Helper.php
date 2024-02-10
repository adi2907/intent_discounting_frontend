<?php

if (!function_exists('getShopifyAPIURLForStore')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function getShopifyAPIURLForStore($path, $shop, $apiVersion = null)
    {
        $shop_url = $shop['myshopify_domain'] ?? $shop['shop_url'];
        $apiVersion = $apiVersion !== null ? $apiVersion : config('shopify.API_VERSION');
        return 'https://'.$shop_url.'/admin/api/'.$apiVersion.'/'.$path;
    }

    function getShopifyAPIHeadersForStore($shop) {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-Shopify-Access-Token' => $shop['accessToken'] ?? $shop['access_token']
        ];
    }

    function getAlmeAppURLForStore($path) {
        return 'https://almeapp.com/'.$path;
    }

    function getAlmeHeaders() {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Origin'=> 'https://www.almeapp.co.in'
        ];
    }
}
