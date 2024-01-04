<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class FinishInstallation implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $store, $user;
    /**
     * Create a new job instance.
     */
    public function __construct($store, $user) {
        $this->store = $store;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->store->notificationSettings()->firstOrCreate([
                'status' => 1,
                'discount_value' => 15,
                'sale_status' => true,
                'sale_discount_value' => 15,
                'discount_expiry' => 8,
                'title' => 'Become an Insider',
                'description' => 'Receive WhatsApp notification of new collection and sales updates'
            ]);
            $this->store->productRackInfo()->firstOrCreate([
                'usersAlsoLiked' => false,
                'featuredCollection' => false,
                'pickUpWhereYouLeftOff' => false,
                'crowdFavorites' => false
            ]);
            Artisan::call('app:sync-orders');
            Artisan::call('app:discount');
            Artisan::call('app:discount');
        } catch (Exception $e) {
            Log::info($e->getMessage().' '.$e->getLine());
        }
        
    }
}
