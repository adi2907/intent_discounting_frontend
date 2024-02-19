<?php

if (!function_exists('getShopifyAPIURLForStore')) {

    /**
     * description
     *
     * @param
     * @return
     */
    function getShopifyAPIURLForStore($path, $store, $apiVersion = null)
    {
        $shop_url = $store['myshopify_domain'] ?? $store['shop_url'];
        $apiVersion = $apiVersion !== null ? $apiVersion : config('shopify.API_VERSION');
        return 'https://'.$shop_url.'/admin/api/'.$apiVersion.'/'.$path;
    }

    function getShopifyAPIHeadersForStore($store) {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-Shopify-Access-Token' => $store['accessToken'] ?? $store['access_token']
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

    function shorten($string, $maxLength) {
        if(strlen($string) > $maxLength) {
            return substr($string, 0, $maxLength).'...';
        }
        
        return $string;
    } 
}
