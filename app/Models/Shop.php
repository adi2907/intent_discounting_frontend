<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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

    public function getNotificationStats() {
        $cacheKey = $this->id.'_notif_stats';
        if(Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        return null;
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

    public function getContactCaptureStats() {
        $cacheKey = $this->id.'_contact_stats';
        if(Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        return null;
    }
}
