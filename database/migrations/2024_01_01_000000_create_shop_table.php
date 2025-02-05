<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('shop', function (Blueprint $table) {
            $table->id();  // Primary Key
            $table->string('name');  // Shop Name
            $table->string('shop_url')->unique();  // Unique Shop URL
            $table->string('hmac')->nullable();  // HMAC for authentication
            $table->tinyInteger('isActivated')->default(false);  // Activation Status
            $table->tinyInteger('is_exempt_from_pay')->default(false);  // Payment Exemption
            $table->string('prefix')->nullable();  // Discount Prefix
            $table->timestamps();  // Created & Updated At
        });
    }

    public function down(): void {
        Schema::dropIfExists('shop');
    }
};

