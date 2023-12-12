<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShopifyOrder extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'table_id';
    public $timestamps = false;
}
