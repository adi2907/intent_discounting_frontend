<?php

namespace App\Http\Middleware;

use App\Traits\FunctionTrait;
use App\Traits\RequestTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreIsPaid {
    use FunctionTrait, RequestTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(Auth::check()) {
            $user = Auth::user();
            $shop = $user->shopifyStore;
            if(!$shop->isExemptFromPaying()) {
                $checkIfStoreHasPaid = $shop->checkLastSubscription();
                if($checkIfStoreHasPaid) {
                    return $next($request);
                } else {
                    return $this->redirectShopToPaymentScreen($shop);
                }
            }
            return $next($request);
        }
        return redirect()->route('login');
    }
}
