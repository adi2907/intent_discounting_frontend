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

class SyncIdentifiedUsers implements ShouldQueue
{
    use FunctionTrait, RequestTrait;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $shop;
    /**
     * Create a new job instance.
     */
    public function __construct($shop)
    {
        $this->shop = $shop;
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        $data = $this->callAlmeAppIdentifiedUsers($this->shop);
        if($data['statusCode'] == 200 && is_array($data['body']) && count($data['body']) > 0) {
            IdentifiedUsers::where('shop_id', $this->shop->id)->delete();
            foreach($data['body'] as $info) {
                IdentifiedUsers::insert([
                    'shop_id' => $this->shop->id,
                    'name' => $info['name'],
                    'last_visited' => $info['last_visited'] ?? 'N/A',
                    'email' => $info['email'] ?? 'N/A',
                    'serial_number' => $info['serial_number'] ?? 'N/A',
                    'phone' => $info['phone'] ?? 'N/A',
                    'visited' => $info['visited'] ?? 'N/A',
                    'added_to_cart' => $info['added_to_cart'] ?? 'N/A',
                    'purchased' => $info['purchased'] ?? 'N/A'
                ]);
            }
        }
    }
}
