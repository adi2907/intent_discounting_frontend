<?php

namespace App\Jobs;

use App\Models\PriceRule;
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
use Throwable;

class CreateShopDiscountCode implements ShouldQueue {

    use FunctionTrait, RequestTrait;
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
        $shop = $this->shop;
        $hasEnabledDiscounts = $this->shopHasEnabledDiscountSettings($shop);
        if($this->verifyInstallation($shop) && $hasEnabledDiscounts) {
            $priceRule = $shop->getLatestPriceRule;
            if($priceRule !== null && $priceRule->price_id !== null && strlen($priceRule->price_id) > 0) {
                if($this->isPriceRuleValid($priceRule, $shop)) {
                    if($shop->notificationSettings !== null) {
                        $frequency = (int) $shop->notificationSettings->discount_expiry;
                        $lastDiscountCode = $shop->getLatestDiscountCode;
                        if($lastDiscountCode !== null && $lastDiscountCode->count() > 0) {
                            $hourdiff = round((strtotime('today UTC') - strtotime($lastDiscountCode->created_at))/3600, 1);
                            
                            if($hourdiff >= $frequency) {
                                $this->deletePriceRule($priceRule, $shop);
                                $this->createPriceRuleForShop($shop);
                                $shop->refresh('getLatestPriceRule');
                                $this->createAndSaveDiscountCode($shop->getLatestPriceRule, $shop, $frequency);
                            }
                        } else {
                            //No Discount exist so create one.
                            $this->createAndSaveDiscountCode($priceRule, $shop, $frequency);
                        }
                    }
                } else {
                    PriceRule::where('id', $shop->getLatestPriceRule->id)->delete();
                    $this->createPriceRuleForShop($shop);
                    //Log::info('Problem with validity for price rule '.$shop->id.' '.$shop->shop_url);
                }  
            } else {
                $this->createPriceRuleForShop($shop);
                //$this->createAndSaveDiscountCode($priceRule, $shop);
            }
        }
    }

    private function shopHasEnabledDiscountSettings($shop) {
        try {
            if(isset($shop->notificationSettings) && isset($shop->notificationSettings->sale_status)) {
                return $shop->notificationSettings->sale_status == 1;
            }
            return false; 
        } catch (Throwable $th) {
            return false;
        }
    }

    /**
     * "almeapp.com/api/carts/?token=q6wxm4v47y9&max_items=5&app_name=test_shopify"
     *"almeapp.com/api/visits/?token=q6wxm4v47y9&app_name=test_shopify&max_items=5"
     *"almeapp.com/api/most_visited/?app_name=test_shopify"
     *"almeapp.com/api/most_carted/?app_name=test_shopify" 
    */
}
