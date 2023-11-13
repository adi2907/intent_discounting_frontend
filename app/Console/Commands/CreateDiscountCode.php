<?php

namespace App\Console\Commands;

use App\Models\Shop;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CreateDiscountCode extends Command {

    use RequestTrait, FunctionTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:discount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates price rules (if any arent created yet) and then creates discount code on them per day basis.';

    /**
     * Execute the console command.
     */
    public function handle() {

        $cacheKey = config('custom.cacheKeys.createDiscountCode');
        $data = [
            'ok' => true,
            'last_update' => date('Y/m/d h:i:s')
        ];
        Cache::set($cacheKey, $data);

        $shops = Shop::with(['notificationSettings', 'getLatestPriceRule', 'getLatestDiscountCode'])->get();
        foreach($shops as $shop) {
            if($this->verifyInstallation($shop)) {
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
                                    $this->createAndSaveDiscountCode($shop->getLatestPriceRule, $shop);
                                } else {
                                    Log::info('Creating discount code not carried out');
                                }
                            } else {
                                //No Discount exist so create one.
                                $this->createAndSaveDiscountCode($priceRule, $shop);
                            }

                        }
                    } else {
                        Log::info('Problem with validity for price rule '.$shop->id.' '.$shop->shop_url);
                    }  
                } else {
                    $this->createPriceRuleForShop($shop);
                    //$this->createAndSaveDiscountCode($priceRule, $shop);
                }
            } else {
                Log::info('Store Installation not valid for shop '.$shop->id.' '.$shop->shop_url);
            }
        }
        $this->info('=========================================');
        $this->info('FINISHED');
        $this->info('=========================================');
    }

    private function deletePriceRule($priceRule, $shop) {
        if(isset($priceRule->price_id) && $priceRule->price_id !== null) {
            $endpoint = getShopifyAPIURLForStore('price_rules/'.$priceRule->price_id.'.json', $shop);
            $headers = getShopifyAPIHeadersForStore($shop);
            $this->makeAnAPICallToShopify('DELETE', $endpoint, $headers);
        }
    }

    private function createAndSaveDiscountCode($priceRule, $shop) {
        $data = $this->createDiscountCode($priceRule, $shop);
        if(array_key_exists('code', $data) && $data['code'] !== null && strlen($data['code']) > 0) {
            $shop->getDiscountCode()->create([
                'code' => $data['code'],
                'full_response' => json_encode($data)
            ]);
            $this->info('Created and saved discount code for '.$shop->shop_url);
        } else {
            Log::info('here Problem with validity for price rule '.$shop->id.' '.$shop->shop_url);
            Log::info($data);
        }
    }

    private function createPriceRuleForShop($shop) {
        //Create the price rule and save it
        $endpoint = getShopifyAPIURLForStore('price_rules.json', $shop);
        $headers = getShopifyAPIHeadersForStore($shop);

        $saleDiscountValue = 10;
        try {
            $saleDiscountValue = isset($shop->notificationSettings) && $shop->notificationSettings->count() > 0 ? $shop->notificationSettings->sale_discount_value : null;
        } catch(Exception $e) {
            $saleDiscountValue = 10;
        } 
        $saleDiscountValue = '-'.$saleDiscountValue;
        $startsAt = date('c');
        $this->info('Discount '.$saleDiscountValue);
        $this->info('Setting start as '.$startsAt);


        $payload = [
            'price_rule' => [
                "title" => "ALMEPRICERULE",
                "target_type" => "line_item",
                "target_selection" => "all",
                "allocation_method" => "across",
                "value_type" => "percentage",
                "value" => $saleDiscountValue,
                "customer_selection" => "all",
                "starts_at" => date('c')
            ]
        ];

        try{
            $discountExpiry = isset($shop->notificationSettings) && $shop->notificationSettings->count() > 0 ? $shop->notificationSettings->discount_expiry : null;
        } catch(Exception $e) {
            $discountExpiry = null;
        }
        if($discountExpiry !== null) {
            $discountExpiry = (int) $discountExpiry;
            $strtotime = strtotime('+'.($discountExpiry * 2).' hours');
            $endsAt = date('c', $strtotime);
            $this->info('Setting ends at '.$endsAt);
            $payload['price_rule'] = array_merge($payload['price_rule'], ['ends_at' => $endsAt]);
        }

        $response = $this->makeAnAPICallToShopify('POST', $endpoint, $headers, $payload);
        if(array_key_exists('statusCode', $response) && $response['statusCode'] == 201) {
            $priceRuleId = $response['body']['price_rule']['id'];
            $fullResponse = json_encode($response['body']['price_rule']);
            $shop->getPriceRule()->create([
                'price_id' => $priceRuleId,
                'full_response' => $fullResponse
            ]);
        } else {
            Log::info('Problem while creating price rule');
            Log::info($response);
        }
        
        return true;
    }
}
