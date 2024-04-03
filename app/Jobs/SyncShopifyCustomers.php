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

class SyncShopifyCustomers implements ShouldQueue
{
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
            $customers = [];
            do {
                $endpoint = getShopifyAPIURLForStore('customers.json?since_id='.$since_id, $this->shop);
                $response = $this->makeAnAPICallToShopify('GET', $endpoint, $headers);

                $customers = $response['statusCode'] == 200 ? $response['body']['customers'] ?? null : null;
                if($customers !== null)
                    foreach($customers as $customer) {
                        $this->updateOrCreateThisCustomerInDB($customer, $this->shop);
                        $since_id = $customer['id'];
                    }
            } while($customers !== null && count($customers) > 0);
        } catch(Exception $e) {
            Log::info('Error syncing customers shop id '.$this->shop->id.' url '.$this->shop->shop_url);
            Log::info($e->getMessage().' '.$e->getLine());
        }
    }
}
