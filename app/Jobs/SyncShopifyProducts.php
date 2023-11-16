<?php

namespace App\Jobs;

use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncShopifyProducts implements ShouldQueue {

    use RequestTrait, FunctionTrait;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $shop;
    /**
     * Create a new job instance.
     */
    public function __construct($shop) {
        $this->shop = $shop;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {    
        try {
            $since_id = 0;
            $headers = getShopifyAPIHeadersForStore($this->shop);
            $products = [];
            do {
                $endpoint = getShopifyAPIURLForStore('products.json?since_id='.$since_id, $this->shop);
                $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
                $products = $response['statusCode'] == 200 ? $response['body']['products'] ?? null : null;
                foreach($products as $product) {
                    $this->updateOrCreateThisProductInDB($product, $this->shop);
                    $since_id = $product['id'];
                }
            } while($products !== null && count($products) > 0);
        } catch(Exception $e) {
            Log::info('Error syncing products shop id '.$this->shop->id.' url '.$this->shop->shop_url);
            Log::info($e->getMessage().' '.$e->getLine());
        }
    }
}
