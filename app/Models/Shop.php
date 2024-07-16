<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Shop extends Model {
    
    use HasFactory;
    protected $guarded = [];
    protected $table = 'shop';
    public $timestamps = false;

    public function getPriceRule() {
        return $this->hasOne(PriceRule::class, 'store_id', 'id');
    }

    public function getPriceRules() {
        return $this->hasMany(PriceRule::class, 'store_id', 'id');
    }

    public function getLatestPriceRule() {
        return $this->hasOne(PriceRule::class, 'store_id', 'id')->orderBy('created_at', 'desc');
    }

    public function getSecondLatestPriceRule() {
        try {
            $priceRules = $this->getPriceRules()
                ->orderBy('created_at', 'desc')
                ->limit(2)
                ->get();
            
            if ($priceRules->count() > 1) {
                return $priceRules->last(); // Get the second latest price rule
            }
            
            return null;
        } catch (Exception $e) {
            Log::error('Error fetching second latest price rule: ' . $e->getMessage());
            return null;
        }
    }

    public function getLatestDiscountCode() {
        return $this->hasOne(DiscountCode::class, 'store_id', 'id')->orderBy('created_at', 'desc');
    }

    public function getDiscountCode() {
        return $this->hasOne(DiscountCode::class, 'store_id', 'id');
    }

    public function getDiscountCodes() {
        return $this->hasMany(DiscountCode::class, 'store_id', 'id');
    }

    public function notificationSettings() {
        return $this->hasOne(NotificationSettings::class, 'store_id', 'id')->orderBy('id', 'desc');
    }

    public function notificationAsset() {
        return $this->hasOne(NotificationAsset::class, 'store_id', 'id')->orderBy('id', 'desc');
    }

    public function productRackInfo() {
        return $this->hasOne(ProductRackSettings::class, 'store_id', 'id')->orderBy('id', 'desc');
    }

    public function getOrders() {
        return $this->hasMany(ShopifyOrder::class, 'shop_id', 'id');
    }

    public function getProducts() {
        return $this->hasMany(ShopifyProducts::class, 'shop_id', 'id');
    }

    public function getIdentifiedUsers() {
        return $this->hasMany(IdentifiedUsers::class, 'shop_id', 'id')->where(function ($query) {
            return $query->orWhere('email', '<>', 'N/A')->orWhere('phone', '<>', 'N/A');
        });
    }

    public function getAlmeWebhookEvents() {
        return $this->hasMany(AlmeWebhookEvent::class, 'shop_id', 'id');
    }

    public function isActivated() {
        return (bool) $this->isActivated;
    }

    public function getAudienceSegments() {
        return $this->hasMany(SegmentRule::class, 'shop_id', 'id');
    }

    public function getClickAnalytics() {
        return $this->hasMany(AlmeClickAnalytics::class, 'shop_id', 'id');
    }

    public function getNotificationStats() {
        $cacheKey = $this->id.'_notif_stats';
        if(Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        return null;
    }

    public function getSaleNotifStats($startDate = null, $endDate = null) {
        try {
            
            $impressions = $this->getClickAnalytics()
            ->whereNotNull('sale_notif_impression')
            ->where('sale_notif_impression', '>', 0)
            ->where(function ($query) use($startDate, $endDate) {
                if($startDate != null && $endDate != null) {
                    return $query->where('created_at', '>', date('Y-m-d 00:00:00', strtotime($startDate)))
                    ->where('created_at', '<', date('Y-m-d 23:59:59', strtotime($endDate)));
                }
                return true;
            })
            ->count();
            
            $clicks = $this->getClickAnalytics()
            ->whereNotNull('sale_notif_click')
            ->where('sale_notif_click', '>', 0)
            ->where(function ($query) use($startDate, $endDate) {
                if($startDate != null && $endDate != null) {
                    return $query->where('created_at', '>', date('Y-m-d 00:00:00', strtotime($startDate)))
                    ->where('created_at', '<', date('Y-m-d 23:59:59', strtotime($endDate)));
                }
                return true;
            })
            ->count();
            
            $redemptions = $this->getClickAnalytics()
            ->whereNotNull('order_id')
            ->where(function ($query) use($startDate, $endDate) {
                if($startDate != null && $endDate != null) {
                    return $query->where('created_at', '>', date('Y-m-d 00:00:00', strtotime($startDate)))
                    ->where('created_at', '<', date('Y-m-d 23:59:59', strtotime($endDate)));
                }
                return true;
            })
            ->whereNotNull('discount_id')
            ->count();
            
            return [
                'impressions' => $impressions,
                'copy_events' => $clicks,
                'redemptions' => $redemptions
            ];
        } catch(Exception $e) {
            return [
                'impressions' => 0,
                'copy_events' => 0,
                'coupon_redemptions' => 0,
                'error' => $e->getMessage().' '.$e->getLine()
            ];
        }
    }

    public function setNotifStats($stats) {
        $cacheKey = $this->id.'_notif_stats';
        Cache::put($cacheKey, $stats);
        return true;
    }

    public function setContactCaptureStats($stats) {
        $cacheKey = $this->id.'_contact_stats';
        Cache::put($cacheKey, $stats);
        return true;
    }

    public function getContactCaptureStats($startDate = null, $endDate = null) {
        try {

            $impressions = $this->getClickAnalytics()
            ->whereNotNull('contact_notif_impression')
            ->where('contact_notif_impression', '>', 0)
            ->where(function ($query) use($startDate, $endDate) {
                if($startDate != null && $endDate != null) {
                    return $query->where('created_at', '>', date('Y-m-d 00:00:00', strtotime($startDate)))
                    ->where('created_at', '<', date('Y-m-d 23:59:59', strtotime($endDate)));
                }
                return true;
            })
            ->count();
            
            $clicks = $this->getClickAnalytics()
            ->whereNotNull('contact_notif_click')
            ->where('contact_notif_click', '>', 0)
            ->where(function ($query) use($startDate, $endDate) {
                if($startDate != null && $endDate != null) {
                    return $query->where('created_at', '>', date('Y-m-d 00:00:00', strtotime($startDate)))
                    ->where('created_at', '<', date('Y-m-d 23:59:59', strtotime($endDate)));
                }
                return true;
            })
            ->count();
            
            return [
                'impressions' => $impressions,
                'submit_events' => $clicks,
            ];
        } catch(Exception $e) {
            return [
                'impressions' => 0,
                'submit_events' => 0,
                'error' => $e->getMessage().' '.$e->getLine()
            ];
        }
    }
}
