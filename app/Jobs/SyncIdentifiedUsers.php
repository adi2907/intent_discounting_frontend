<?php

namespace App\Jobs;

use App\Models\IdentifiedUsers;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncIdentifiedUsers implements ShouldQueue {

    use FunctionTrait, RequestTrait;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $shop;
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
        $data = $this->callAlmeAppIdentifiedUsers($this->shop);
        if($data['statusCode'] == 200 && is_array($data['body']) && count($data['body']) > 0) {
            //IdentifiedUsers::where('shop_id', $this->shop->id)->delete();
            foreach($data['body'] as $info) {
                $customerResp = $this->getShopCustomer($this->shop, $info['regd_user_id']);
                $customerFullName = null;
                $customerEmail = null;
                $customerPhone = null;
                try {
                    $customerFullName = $customerResp['body']['customer']['first_name'].' '.$customerResp['body']['customer']['last_name'];
                    $customerEmail = $customerResp['body']['customer']['email'];
                    $customerPhone = $customerResp['body']['customer']['phone'];
                } catch (\Throwable $th) {
                    $customerFullName = null;
                    $customerEmail = null;
                    $customerPhone = null;
                }

                $createArr = [
                    'shop_id' => $this->shop->id,
                    'regd_user_id' => isset($info['regd_user_id']) && $info['regd_user_id'] > 0 ? $info['regd_user_id'] : 0,
                    'name' => $customerFullName !== null ? $customerFullName : $info['name'] ?? 'N/A',
                    'last_visited' => date('Y-m-d h:i:s', strtotime($info['last_visited'])) ?? 'N/A',
                    'email' => $customerEmail ?? 'N/A',
                    'serial_number' => $info['serial_number'] ?? 'N/A',
                    'phone' => $customerPhone !== null ? $customerPhone : $info['phone'] ?? 'N/A',
                    'visited' => $info['visited'] ?? 'N/A',
                    'added_to_cart' => $info['added_to_cart'] ?? 'N/A',
                    'purchased' => $info['purchased'] ?? 'N/A'
                ];

                $updateArr = ['shop_id' => $this->shop->id, 'regd_user_id' => $info['regd_user_id']];
            
                IdentifiedUsers::updateOrCreate($updateArr, $createArr);
            }
        }
    }

    public function getShopCustomer($shop, $id) {
        $endpoint = getShopifyAPIURLForStore('customers/'.$id.'.json', $shop);
        $headers = getShopifyAPIHeadersForStore($shop);
        return $this->makeAnAPICallToShopify('GET', $endpoint, $headers);
    }
}
