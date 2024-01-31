<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model {
    
    use HasFactory;
    protected $guarded = [];
    protected $table = 'shop';
    public $timestamps = false;

    public function getPriceRule() {
        return $this->hasOne(PriceRule::class, 'store_id', 'id');
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

    public function notificationSettings() {
        return $this->hasOne(NotificationSettings::class, 'store_id', 'id')->orderBy('id', 'desc');
    }

    public function productRackInfo() {
        return $this->hasOne(ProductRackSettings::class, 'store_id', 'id')->orderBy('id', 'desc');
    }

    public function getProducts() {
        return $this->hasMany(ShopifyProducts::class, 'shop_id', 'id');
    }

    public function getIdentifiedUsers() {
        return $this->hasMany(IdentifiedUsers::class, 'shop_id', 'id');
    }

    public function getAlmeWebhookEvents() {
        return $this->hasMany(AlmeWebhookEvent::class, 'shop_id', 'id');
    }
}
