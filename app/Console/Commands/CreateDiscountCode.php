<?php

namespace App\Console\Commands;

use App\Jobs\CreateShopDiscountCode;
use App\Models\PriceRule;
use App\Models\Shop;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

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
        Cache::set($cacheKey, $data); //Set the last time it was run

        $shops = Shop::with(['notificationSettings', 'getLatestPriceRule', 'getLatestDiscountCode'])->get();
        foreach($shops as $shop) {
            CreateShopDiscountCode::dispatch($shop)->onConnection('sync');
        }
    }
}
