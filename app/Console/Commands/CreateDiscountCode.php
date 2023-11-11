<?php

namespace App\Console\Commands;

use App\Models\Shop;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Console\Command;
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

        $shops = Shop::with('getLatestPriceRule')->get();
        foreach($shops as $shop) {
            if($this->verifyInstallation($shop)) {
                $priceRule = $shop->getPriceRule;
                if($priceRule !== null && $priceRule->price_id !== null && strlen($priceRule->price_id) > 0) {
                    if($this->isPriceRuleValid($priceRule, $shop)) {
                        $this->createAndSaveDiscountCode($priceRule, $shop);
                    } else {
                        Log::info('Problem with validity for price rule '.$shop->id.' '.$shop->shop_url);
                    }  
                } else {
                    $this->createPriceRuleForShop($shop);
                }
            } else {
                Log::info('Store Installation not valid for shop '.$shop->id.' '.$shop->shop_url);
            }
        }
        $this->info('=========================================');
        $this->info('FINISHED');
        $this->info('=========================================');
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
        $payload = [
            'price_rule' => [
                "title" => "ALMEPRICERULE",
                "target_type" => "line_item",
                "target_selection" => "all",
                "allocation_method" => "across",
                "value_type" => "percentage",
                "value" => "-10",
                "customer_selection" => "all",
                "starts_at" => date('Y-m-d').'T00:00:00Z'
            ]
        ];
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
