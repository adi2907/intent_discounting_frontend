<?php

namespace App\Jobs;

use App\Models\ShopifyProducts;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ConfigureMissingProducts implements ShouldQueue {

    public $shop, $events;
    use FunctionTrait, RequestTrait;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct($shop, $events) {
        $this->shop = $shop;
        $this->events = $events;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        try {
            $events = $this->events;
            $shop = $this->shop;

            //First collect all productIds from the events array
            $productIdsArr = [];
            if(is_array($events) && count($events) > 0) {
                foreach($events as $event) {
                    if(array_key_exists('product_id', $event) && $event['product_id'] !== null && strlen($event['product_id']) > 0) {
                        $productIdsArr[] = $event['product_id'];
                    }
                } 
            }

            if(count($productIdsArr) > 0) {
                //Filter null/epmty values and make productIdsArr unique
                $productIdsArr = array_unique(array_filter($productIdsArr, 'strlen'));

                //Select distinct product ids from the database given that we have seperate records for each product variant
                $records = ShopifyProducts::where('shop_id', $shop['id'])
                                          ->whereIn('product_id', $productIdsArr)
                                          ->distinct('product_id')
                                          ->get()
                                          ->keyBy('product_id')
                                          ->toArray();
                
                if($records !== null && count($records) > 0) {
                    if(count($records) != count($productIdsArr)) {
                        //Now we know that some product is missing from the database that we need to sync
                        $dbProductIds = array_keys($records);
                        $nonExistentKeys = array_diff($productIdsArr, $dbProductIds);
                        if($nonExistentKeys !== null && count($nonExistentKeys) > 0) {
                            //Log::info('Product Ids not found in database for shop '.$shop->shop_url);
                            foreach($nonExistentKeys as $productId) {
                                $endpoint = getShopifyAPIURLForStore('products/'.$productId.'.json', $shop);
                                $headers = getShopifyAPIHeadersForStore($shop);
                                $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
                                if($response['statusCode'] == 200) {
                                    //Log::info('Syncing product Id '.$productId.' into database for shop '.$shop->shop_url);
                                    $this->updateOrCreateThisProductInDB($response['body']['product'], $shop);
                                }
                            }
                        }
                    }
                }            
            }
        } catch (Throwable $th) {
            Log::info('Error in Configure Missing product');
            Log::info($th->getMessage().' '.$th->getLine());
        }
    }
}
