<?php

namespace App\Console\Commands;

use App\Models\Shop;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Console\Command;

class SetDashboard extends Command {

    use FunctionTrait, RequestTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-dashboard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets the dashboard payload for shops';

    /**
     * Execute the console command.
     */
    public function handle() {
        $shops = Shop::get();
        foreach($shops as $shop) {
            $this->getAlmeAnalytics($shop->shop_url, null, true);
        }
        $this->info('Done');
    }
}
