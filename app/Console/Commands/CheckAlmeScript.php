<?php

namespace App\Console\Commands;

use App\Models\Shop;
use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckAlmeScript extends Command
{
    use FunctionTrait, RequestTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:alme-script';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks if the script tag is turned on for each store';

    /**
     * Execute the console command.
     */
    public function handle() {
        $shops = Shop::get();
        foreach($shops as $shop) {
            if($this->verifyInstallation($shop)) {
                $result = $this->checkAlmeScriptRunningOrNot($shop);
                Cache::put('Alme:Scripts:Stores.'.$shop->shop_url, $result);
            }
        }
    }
}
