<?php

namespace App\Http\Middleware;

use App\Models\Shop;
use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * @return array<int, string|null>
     */
    public function hosts(): array
    {

        $shops = Shop::select(['shop_url'])->get()->pluck('shop_url')->toArray();
        $finalArr = [];
        $finalArr[] = $this->allSubdomainsOfApplicationUrl();
        foreach($shops as $shop) {
            $finalArr[] = 'https://'.$shop;
        }
        //dd($finalArr);
        return $finalArr;
    }
}
