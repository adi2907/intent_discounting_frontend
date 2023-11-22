<?php

namespace App\Jobs;

use App\Models\Shop;
use Illuminate\Bus\Queueable;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class RegisterWebhooks implements ShouldQueue
{
    use FunctionTrait, RequestTrait;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $shop_id;
    /**
     * Create a new job instance.
     */
    public function __construct($shop_id)
    {
        $this->shop_id = $shop_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $store = Shop::where('id', $this->shop_id)->first();
            $endpoint = getShopifyAPIURLForStore('webhooks.json', $store);
            $headers = getShopifyAPIHeadersForStore($store);
            $webhooksList = config('shopify.webhooks');
            foreach($webhooksList as $topic => $routeName) {
                $body = [
                    'webhook' => [
                        'topic' => $topic,
                        'address' => route($routeName),
                        'format' => 'json'
                    ]
                ];
                $response = $this->makeAnAPICallToShopify('POST', $endpoint, $headers, $body);
                Log::info('Response for topic '.$topic);
                Log::info($response['body']);
                //You can write a logic to save this in the database table.
            }
        } catch(Exception $e) {
            //Log::info(json_encode($e->getTrace()));
            Log::info('here in configure webhooks ' . $e->getMessage().' '.$e->getLine());
        }
    }
}
