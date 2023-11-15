<?php

if (!function_exists('getShopifyAPIURLForStore')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function getShopifyAPIURLForStore($path, $store)
    {
        $shop_url = $store['myshopify_domain'] ?? $store['shop_url'];
        return 'https://'.$shop_url.'/admin/api/'.config('shopify.API_VERSION').'/'.$path;
    }

    function getShopifyAPIHeadersForStore($store) {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-Shopify-Access-Token' => $store['accessToken'] ?? $store['access_token']
        ];
    }

    function getAlmeAppURLForStore($path) {
        return 'https://almeapp.com/api/'.$path;
    }

    function getAlmeHeaders() {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
    }
}
